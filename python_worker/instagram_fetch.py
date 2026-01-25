#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
IGReelDownloader.net

Supports: Reels, Videos, Photos, Stories, Carousel posts.
- Uses yt-dlp for video content
- Uses direct HTTP requests for photo content

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_json> [yt_dlp_path]
"""

import sys
import os
import json
import subprocess
import re
import shutil
import hashlib
import time
from pathlib import Path
from urllib.parse import urlparse

# Try to import requests, fall back to urllib if not available
try:
    import requests
    HAS_REQUESTS = True
except ImportError:
    import urllib.request
    import urllib.error
    import http.cookiejar
    HAS_REQUESTS = False


def log_error(message, error_type="unknown", cookies_tried=0, debug_info=None):
    """Output error as JSON and exit."""
    output = {
        "success": False,
        "error": str(message)[:500],
        "error_type": error_type,
        "cookies_tried": cookies_tried
    }
    if debug_info:
        output["debug"] = debug_info
    print(json.dumps(output))
    sys.exit(1)


def log_debug(message):
    """Log debug message to stderr."""
    sys.stderr.write(f"[DEBUG] {message}\n")
    sys.stderr.flush()


def validate_url(url):
    """Validate Instagram URL format."""
    patterns = [
        r'^https?://(www\.)?instagram\.com/p/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/reel/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/reels/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/tv/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/stories/[\w.]+/\d+/?',
    ]
    for pattern in patterns:
        if re.match(pattern, url, re.IGNORECASE):
            return True
    return False


def get_env():
    """Get environment variables for subprocess."""
    env = os.environ.copy()
    if 'HOME' not in env or not env['HOME']:
        env['HOME'] = '/tmp'
    path_additions = ['/usr/local/bin', '/usr/bin', '/bin', '/home/ubuntu/.local/bin']
    current_path = env.get('PATH', '')
    for p in path_additions:
        if p not in current_path:
            current_path = p + ':' + current_path
    env['PATH'] = current_path
    return env


def find_ytdlp_binary(ytdlp_input):
    """Find a working yt-dlp binary."""
    candidates = []
    if ytdlp_input and ytdlp_input.strip():
        candidates.append(ytdlp_input.strip())
    candidates.extend([
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
        '/root/.local/bin/yt-dlp',
        'yt-dlp',
    ])
    which_result = shutil.which('yt-dlp')
    if which_result:
        candidates.insert(1, which_result)
    
    seen = set()
    unique_candidates = []
    for c in candidates:
        if c and c not in seen:
            seen.add(c)
            unique_candidates.append(c)
    
    for candidate in unique_candidates:
        if not candidate:
            continue
        try:
            if os.path.isfile(candidate) and os.access(candidate, os.X_OK):
                return candidate
            result = subprocess.run(
                [candidate, '--version'],
                capture_output=True,
                text=True,
                timeout=10,
                env=get_env()
            )
            if result.returncode == 0:
                return candidate
        except Exception:
            continue
    return None


def run_ytdlp(args, timeout=120):
    """Run yt-dlp with proper error handling."""
    try:
        result = subprocess.run(
            args,
            capture_output=True,
            text=True,
            timeout=timeout,
            env=get_env()
        )
        return result.returncode, result.stdout, result.stderr
    except subprocess.TimeoutExpired:
        return -2, '', 'Request timed out'
    except FileNotFoundError as e:
        return -3, '', f'Binary not found: {str(e)}'
    except Exception as e:
        return -5, '', f'Unexpected error: {str(e)}'


def parse_netscape_cookies(cookie_file):
    """Parse Netscape format cookie file and return cookies dict for requests."""
    cookies = {}
    try:
        with open(cookie_file, 'r', encoding='utf-8', errors='ignore') as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith('#'):
                    continue
                parts = line.split('\t')
                if len(parts) >= 7:
                    domain, _, path, secure, expires, name, value = parts[:7]
                    if 'instagram.com' in domain:
                        cookies[name] = value
    except Exception as e:
        log_debug(f"Error parsing cookies: {e}")
    return cookies


def download_image_with_requests(url, save_path, cookies_dict):
    """Download image using requests library."""
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
        'Referer': 'https://www.instagram.com/',
        'Sec-Fetch-Dest': 'image',
        'Sec-Fetch-Mode': 'no-cors',
        'Sec-Fetch-Site': 'cross-site',
    }
    
    try:
        if HAS_REQUESTS:
            session = requests.Session()
            session.cookies.update(cookies_dict)
            response = session.get(url, headers=headers, timeout=30, allow_redirects=True)
            response.raise_for_status()
            
            with open(save_path, 'wb') as f:
                f.write(response.content)
            return True
        else:
            # Fallback to urllib
            req = urllib.request.Request(url, headers=headers)
            
            # Add cookies to request
            cookie_header = '; '.join([f'{k}={v}' for k, v in cookies_dict.items()])
            req.add_header('Cookie', cookie_header)
            
            with urllib.request.urlopen(req, timeout=30) as response:
                with open(save_path, 'wb') as f:
                    f.write(response.read())
            return True
    except Exception as e:
        log_debug(f"Error downloading image: {e}")
        return False


def get_image_extension(url, content_type=None):
    """Determine image extension from URL or content type."""
    url_lower = url.lower()
    if '.jpg' in url_lower or '.jpeg' in url_lower:
        return 'jpg'
    if '.png' in url_lower:
        return 'png'
    if '.webp' in url_lower:
        return 'webp'
    if '.gif' in url_lower:
        return 'gif'
    if content_type:
        if 'jpeg' in content_type or 'jpg' in content_type:
            return 'jpg'
        if 'png' in content_type:
            return 'png'
        if 'webp' in content_type:
            return 'webp'
        if 'gif' in content_type:
            return 'gif'
    return 'jpg'  # Default


def fetch_metadata(url, cookies_path, ytdlp_bin):
    """Fetch metadata using yt-dlp --dump-json."""
    cmd = [
        ytdlp_bin,
        '--cookies', cookies_path,
        '--dump-json',
        '--no-download',
        '--no-warnings',
        '--no-check-certificates',
        '--socket-timeout', '30',
        '--extractor-args', 'instagram:api_only=false',
        url
    ]

    log_debug(f"Fetching metadata with cookie: {os.path.basename(cookies_path)}")
    return_code, stdout, stderr = run_ytdlp(cmd, timeout=60)

    if return_code != 0:
        combined = (stderr + '\n' + stdout).strip()
        return None, combined

    output_lines = stdout.strip().split('\n')
    entries = []

    for line in output_lines:
        if line.strip():
            try:
                entry = json.loads(line)
                entries.append(entry)
            except json.JSONDecodeError:
                continue

    if not entries:
        return None, "No content found at this URL."

    main_info = entries[0].copy()
    if len(entries) > 1:
        main_info['entries'] = entries
        main_info['_type'] = 'playlist'

    return main_info, None


def is_photo_only_error(error_text):
    """Check if error indicates a photo post (no video)."""
    if not error_text:
        return False
    error_lower = error_text.lower()
    photo_indicators = [
        'no video formats found',
        'no video formats',
        'requested format not available',
    ]
    return any(indicator in error_lower for indicator in photo_indicators)


def extract_image_urls_from_page(url, cookies_dict):
    """Extract image URLs by fetching the Instagram page directly."""
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
        'Sec-Fetch-Dest': 'document',
        'Sec-Fetch-Mode': 'navigate',
        'Sec-Fetch-Site': 'none',
    }
    
    try:
        if HAS_REQUESTS:
            session = requests.Session()
            session.cookies.update(cookies_dict)
            response = session.get(url, headers=headers, timeout=30)
            html = response.text
        else:
            req = urllib.request.Request(url, headers=headers)
            cookie_header = '; '.join([f'{k}={v}' for k, v in cookies_dict.items()])
            req.add_header('Cookie', cookie_header)
            with urllib.request.urlopen(req, timeout=30) as response:
                html = response.read().decode('utf-8', errors='ignore')
        
        image_urls = []
        
        # Pattern 1: Look for display_url in JSON
        display_url_pattern = r'"display_url"\s*:\s*"([^"]+)"'
        matches = re.findall(display_url_pattern, html)
        for match in matches:
            decoded_url = match.replace('\\u0026', '&').replace('\\/', '/')
            if decoded_url and 'cdninstagram.com' in decoded_url:
                if decoded_url not in image_urls:
                    image_urls.append(decoded_url)
        
        # Pattern 2: Look for image_versions2 -> candidates
        candidates_pattern = r'"candidates"\s*:\s*\[(.*?)\]'
        candidates_matches = re.findall(candidates_pattern, html, re.DOTALL)
        for candidates_str in candidates_matches:
            url_pattern = r'"url"\s*:\s*"([^"]+)"'
            urls = re.findall(url_pattern, candidates_str)
            for img_url in urls:
                decoded_url = img_url.replace('\\u0026', '&').replace('\\/', '/')
                if decoded_url and 'cdninstagram.com' in decoded_url:
                    if decoded_url not in image_urls:
                        image_urls.append(decoded_url)
        
        # Pattern 3: og:image meta tag
        og_pattern = r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']'
        og_matches = re.findall(og_pattern, html)
        for match in og_matches:
            decoded_url = match.replace('&amp;', '&')
            if decoded_url and 'cdninstagram.com' in decoded_url:
                if decoded_url not in image_urls:
                    image_urls.append(decoded_url)
        
        # Pattern 4: Look in SharedData or additional_data
        shared_data_pattern = r'window\._sharedData\s*=\s*(\{.*?\});</script>'
        shared_matches = re.findall(shared_data_pattern, html, re.DOTALL)
        for match in shared_matches:
            try:
                data = json.loads(match)
                # Navigate through the structure to find images
                if 'entry_data' in data:
                    for page_type in ['PostPage', 'ReelPage']:
                        if page_type in data['entry_data']:
                            for post in data['entry_data'][page_type]:
                                if 'graphql' in post and 'shortcode_media' in post['graphql']:
                                    media = post['graphql']['shortcode_media']
                                    if 'display_url' in media:
                                        if media['display_url'] not in image_urls:
                                            image_urls.append(media['display_url'])
                                    # Check carousel
                                    if 'edge_sidecar_to_children' in media:
                                        for edge in media['edge_sidecar_to_children'].get('edges', []):
                                            node = edge.get('node', {})
                                            if 'display_url' in node:
                                                if node['display_url'] not in image_urls:
                                                    image_urls.append(node['display_url'])
            except json.JSONDecodeError:
                pass
        
        log_debug(f"Found {len(image_urls)} image URLs from page")
        return image_urls
        
    except Exception as e:
        log_debug(f"Error extracting images from page: {e}")
        return []


def download_photo_content(url, download_path, cookies_path, info_dict=None):
    """Download photo content from Instagram."""
    Path(download_path).mkdir(parents=True, exist_ok=True)
    
    cookies_dict = parse_netscape_cookies(cookies_path)
    log_debug(f"Parsed {len(cookies_dict)} cookies from file")
    
    image_urls = []
    
    # First, try to get URLs from metadata if available
    if info_dict:
        # Check thumbnail (often the full image for photo posts)
        thumbnail = info_dict.get('thumbnail', '')
        if thumbnail and 'cdninstagram.com' in thumbnail:
            image_urls.append(thumbnail)
        
        # Check for thumbnails list
        thumbnails = info_dict.get('thumbnails', [])
        for thumb in thumbnails:
            if isinstance(thumb, dict) and 'url' in thumb:
                if 'cdninstagram.com' in thumb['url']:
                    image_urls.append(thumb['url'])
            elif isinstance(thumb, str) and 'cdninstagram.com' in thumb:
                image_urls.append(thumb)
        
        # Check entries for carousel
        entries = info_dict.get('entries', [])
        for entry in entries:
            if 'thumbnail' in entry:
                image_urls.append(entry['thumbnail'])
    
    # If no URLs found, extract from page HTML
    if not image_urls:
        log_debug("No image URLs in metadata, extracting from page...")
        image_urls = extract_image_urls_from_page(url, cookies_dict)
    
    if not image_urls:
        return None, "Could not find any image URLs in the post."
    
    # Remove duplicates while preserving order
    seen = set()
    unique_urls = []
    for img_url in image_urls:
        # Normalize URL for comparison
        normalized = img_url.split('?')[0] if '?' in img_url else img_url
        if normalized not in seen:
            seen.add(normalized)
            unique_urls.append(img_url)
    
    log_debug(f"Downloading {len(unique_urls)} unique images")
    
    downloaded_files = []
    shortcode = extract_shortcode(url)
    
    for idx, img_url in enumerate(unique_urls[:10]):  # Max 10 images (Instagram carousel limit)
        ext = get_image_extension(img_url)
        filename = f"{shortcode}_{idx + 1:02d}.{ext}"
        save_path = os.path.join(download_path, filename)
        
        log_debug(f"Downloading image {idx + 1}: {img_url[:80]}...")
        
        if download_image_with_requests(img_url, save_path, cookies_dict):
            # Verify file was downloaded and has content
            if os.path.exists(save_path) and os.path.getsize(save_path) > 1000:
                downloaded_files.append(Path(save_path))
                log_debug(f"Successfully downloaded: {filename}")
            else:
                log_debug(f"Downloaded file too small or missing: {filename}")
                if os.path.exists(save_path):
                    os.remove(save_path)
        else:
            log_debug(f"Failed to download image {idx + 1}")
    
    if not downloaded_files:
        return None, "Failed to download any images."
    
    return downloaded_files, None


def extract_shortcode(url):
    """Extract shortcode from Instagram URL."""
    patterns = [
        r'/p/([^/?]+)',
        r'/reel/([^/?]+)',
        r'/reels/([^/?]+)',
        r'/tv/([^/?]+)',
        r'/stories/[^/]+/(\d+)',
    ]
    for pattern in patterns:
        match = re.search(pattern, url)
        if match:
            return match.group(1)
    return hashlib.md5(url.encode()).hexdigest()[:12]


def get_content_type(url, info_dict=None, is_photo=False):
    """Determine content type from URL and metadata."""
    url_lower = url.lower()
    
    if '/reel/' in url_lower or '/reels/' in url_lower:
        return 'reel'
    if '/stories/' in url_lower:
        return 'story'
    if '/tv/' in url_lower:
        return 'video'
    
    if is_photo:
        if info_dict:
            entries = info_dict.get('entries', [])
            if len(entries) > 1:
                return 'carousel'
        return 'photo'
    
    if info_dict:
        entries = info_dict.get('entries', [])
        if len(entries) > 1:
            return 'carousel'
        if info_dict.get('_type') == 'playlist':
            return 'carousel'
        ext = info_dict.get('ext', '')
        if ext in ['mp4', 'webm', 'mkv']:
            return 'video'
        if ext in ['jpg', 'jpeg', 'png', 'webp']:
            return 'photo'
    
    return 'post'


def get_quality_label(info_dict):
    """Get human-readable quality label."""
    if not info_dict:
        return 'Original'
    height = info_dict.get('height', 0) or 0
    width = info_dict.get('width', 0) or 0
    if height >= 1080 or width >= 1920:
        return 'HD 1080p'
    if height >= 720 or width >= 1280:
        return 'HD 720p'
    if height >= 480 or width >= 854:
        return 'SD 480p'
    if height > 0:
        return f'{height}p'
    return 'Original'


def download_video_content(url, download_path, cookies_path, ytdlp_bin, content_type='video'):
    """Download video content using yt-dlp."""
    Path(download_path).mkdir(parents=True, exist_ok=True)
    output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')

    cmd = [
        ytdlp_bin,
        '--cookies', cookies_path,
        '--no-warnings',
        '--no-check-certificates',
        '--no-playlist-reverse',
        '--socket-timeout', '30',
        '-o', output_template,
        '--merge-output-format', 'mp4',
        '--write-thumbnail',
        '--convert-thumbnails', 'jpg',
        url
    ]

    log_debug(f"Downloading video to: {download_path}")
    return_code, stdout, stderr = run_ytdlp(cmd, timeout=300)
    combined_output = (stdout + '\n' + stderr).strip()

    if return_code != 0:
        # Check if any files were downloaded despite error
        downloaded_files = list(Path(download_path).glob('*'))
        media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
        media_files = [f for f in downloaded_files if f.suffix.lower() in media_exts and f.is_file()]
        if media_files:
            return sorted(media_files), None
        return None, combined_output

    # Find downloaded files
    downloaded_files = list(Path(download_path).glob('*'))
    video_files = [f for f in downloaded_files if f.suffix.lower() in {'.mp4', '.webm', '.mkv'} and f.is_file()]
    
    if video_files:
        return sorted(video_files), None
    
    # Maybe it was actually a photo?
    image_files = [f for f in downloaded_files if f.suffix.lower() in {'.jpg', '.jpeg', '.png', '.webp'} and f.is_file()]
    if image_files:
        return sorted(image_files), None
    
    return None, "No media files were downloaded."


def try_download(url, download_path, cookie_path, ytdlp_bin, cookie_index):
    """Try to download content with a specific cookie file."""
    cookie_name = os.path.basename(cookie_path)
    log_debug(f"Trying cookie #{cookie_index + 1}: {cookie_name}")

    if not os.path.isfile(cookie_path):
        return None, f"Cookie file not found: {cookie_name}", "cookie_not_found", True

    try:
        file_size = os.path.getsize(cookie_path)
        if file_size < 50:
            return None, f"Cookie file too small: {cookie_name}", "cookie_invalid", True
    except OSError as e:
        return None, f"Cannot read cookie file: {str(e)}", "cookie_unreadable", True

    # First, try to fetch metadata
    info_dict, error_msg = fetch_metadata(url, cookie_path, ytdlp_bin)
    
    is_photo_post = False
    
    if error_msg:
        # Check if it's a "no video formats" error - means it's a photo post
        if is_photo_only_error(error_msg):
            log_debug("Detected photo post (no video formats), switching to photo download...")
            is_photo_post = True
            info_dict = None  # Reset, we'll extract from page
        elif 'private' in error_msg.lower():
            return None, "This content is from a private account.", "private_content", False
        elif 'not found' in error_msg.lower() or '404' in error_msg.lower():
            return None, "This post was not found or has been removed.", "not_found", False
        elif 'login' in error_msg.lower() or 'cookie' in error_msg.lower():
            return None, error_msg, "cookie_error", True
        else:
            # Try as photo anyway
            log_debug(f"Metadata error: {error_msg[:100]}, trying as photo...")
            is_photo_post = True
            info_dict = None
    
    content_type = get_content_type(url, info_dict, is_photo_post)
    log_debug(f"Content type: {content_type}, is_photo: {is_photo_post}")
    
    # Download based on content type
    if is_photo_post or content_type == 'photo':
        media_files, error_msg = download_photo_content(url, download_path, cookie_path, info_dict)
    else:
        # Try video download first
        media_files, error_msg = download_video_content(url, download_path, cookie_path, ytdlp_bin, content_type)
        
        # If video download fails with "no video formats", try photo download
        if error_msg and is_photo_only_error(error_msg):
            log_debug("Video download failed, trying as photo...")
            is_photo_post = True
            content_type = get_content_type(url, info_dict, True)
            media_files, error_msg = download_photo_content(url, download_path, cookie_path, info_dict)
    
    if error_msg:
        return None, error_msg, "download_error", True
    
    if not media_files:
        return None, "No media files downloaded.", "no_media", True
    
    # Build response
    username = 'instagram_user'
    caption = ''
    thumbnail = ''
    
    if info_dict:
        username = info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user'))
        caption = info_dict.get('description', info_dict.get('title', ''))
        thumbnail = info_dict.get('thumbnail', '')
    
    items = []
    for i, file_path in enumerate(media_files):
        ext = file_path.suffix.lower().lstrip('.')
        is_video = ext in ['mp4', 'webm', 'mkv']
        
        # Find thumbnail for videos
        thumb_path = None
        if is_video:
            for thumb in Path(download_path).glob('*.jpg'):
                base_name = file_path.stem.split('_')[0]
                if base_name in thumb.stem and thumb != file_path:
                    thumb_path = str(thumb)
                    break
        
        item = {
            "id": i + 1,
            "type": "video" if is_video else "image",
            "format": ext,
            "quality": get_quality_label(info_dict) if is_video else "Original",
            "path": str(file_path),
            "filename": file_path.name,
            "thumbnail": thumbnail,
            "thumbnail_file": thumb_path or ""
        }
        items.append(item)
    
    # Update content type if multiple items
    if len(items) > 1:
        content_type = 'carousel'
    
    return {
        'info_dict': info_dict,
        'media_files': media_files,
        'content_type': content_type,
        'username': username,
        'caption': caption,
        'thumbnail': thumbnail,
        'items': items,
        'cookie_used': cookie_path
    }, None, None, False


def main():
    if len(sys.argv) < 4:
        log_error(
            "Usage: python instagram_fetch.py <url> <download_path> <cookies_json> [yt_dlp_path]",
            "invalid_args"
        )

    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_json = sys.argv[3]
    ytdlp_input = sys.argv[4] if len(sys.argv) >= 5 else '/usr/local/bin/yt-dlp'

    log_debug(f"URL: {url}")
    log_debug(f"Download path: {download_path}")
    log_debug(f"Has requests library: {HAS_REQUESTS}")

    if not validate_url(url):
        log_error("Invalid Instagram URL format.", "invalid_url")

    # Parse cookies list
    try:
        cookie_files = json.loads(cookies_json)
        if not isinstance(cookie_files, list):
            cookie_files = [cookie_files]
    except json.JSONDecodeError:
        cookie_files = [cookies_json]

    if not cookie_files:
        log_error("No cookie files provided.", "cookies_missing", 0)

    log_debug(f"Cookie files: {len(cookie_files)}")

    # Find yt-dlp binary
    ytdlp_bin = find_ytdlp_binary(ytdlp_input)
    if not ytdlp_bin:
        log_error(f"yt-dlp binary not found: {ytdlp_input}", "ytdlp_missing")

    # Verify yt-dlp works
    return_code, stdout, stderr = run_ytdlp([ytdlp_bin, '--version'], timeout=15)
    if return_code != 0:
        log_error(f"yt-dlp failed: {stderr[:200]}", "ytdlp_crashed")
    log_debug(f"yt-dlp version: {stdout.strip()}")

    # Try each cookie file
    last_error = None
    last_error_type = None
    cookies_tried = 0
    all_errors = []

    for idx, cookie_path in enumerate(cookie_files):
        cookies_tried += 1
        
        result, error_msg, error_type, should_retry = try_download(
            url, download_path, cookie_path, ytdlp_bin, idx
        )

        if result:
            response = {
                "success": True,
                "type": result['content_type'],
                "username": result['username'],
                "caption": (result['caption'][:500] if result['caption'] else ""),
                "thumbnail": result['thumbnail'],
                "items": result['items'],
                "cookies_tried": cookies_tried,
                "cookie_used": os.path.basename(cookie_path)
            }
            print(json.dumps(response))
            sys.exit(0)

        last_error = error_msg
        last_error_type = error_type
        all_errors.append({
            "cookie": os.path.basename(cookie_path),
            "error": error_msg[:200] if error_msg else "Unknown"
        })

        if not should_retry:
            log_debug(f"Permanent error, stopping: {error_msg[:100] if error_msg else 'Unknown'}")
            break

        log_debug(f"Cookie #{idx + 1} failed, trying next...")

    # All cookies failed
    debug_info = {
        "cookies_tried": cookies_tried,
        "all_errors": all_errors,
        "ytdlp_bin": ytdlp_bin,
        "has_requests": HAS_REQUESTS
    }

    if last_error_type == "private_content":
        log_error("This content is from a private account.", "private_content", cookies_tried, debug_info)
    elif last_error_type == "not_found":
        log_error("This post was not found or has been removed.", "not_found", cookies_tried, debug_info)
    else:
        log_error(
            f"All {cookies_tried} cookie(s) failed. Please check cookie files and try again.",
            "all_cookies_failed",
            cookies_tried,
            debug_info
        )


if __name__ == "__main__":
    main()