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


def extract_post_images_from_page(url, cookies_dict, shortcode):
    """
    Extract image URLs specifically for the target post only.
    Uses the shortcode to identify the correct post data.
    """
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
        
        log_debug(f"Fetched page HTML, length: {len(html)}")
        log_debug(f"Looking for shortcode: {shortcode}")
        
        image_urls = []
        username = None
        caption = None
        
        # Method 1: Find the specific media data containing our shortcode
        # Look for the JSON that contains this specific shortcode
        
        # Pattern to find media data blocks
        media_patterns = [
            # Pattern for shortcode_media
            rf'"shortcode"\s*:\s*"{re.escape(shortcode)}"[^{{}}]*"display_url"\s*:\s*"([^"]+)"',
            # Alternative pattern
            rf'"code"\s*:\s*"{re.escape(shortcode)}"[^{{}}]*"display_url"\s*:\s*"([^"]+)"',
        ]
        
        for pattern in media_patterns:
            matches = re.findall(pattern, html, re.DOTALL)
            for match in matches:
                decoded_url = match.replace('\\u0026', '&').replace('\\/', '/')
                if decoded_url and 'cdninstagram.com' in decoded_url:
                    if decoded_url not in image_urls:
                        image_urls.append(decoded_url)
                        log_debug(f"Found image via shortcode pattern: {decoded_url[:80]}")
        
        # Method 2: Look for the xdt_api__v1__media JSON block containing our shortcode
        xdt_pattern = rf'xdt_api__v1__media__shortcode__web_info.*?"shortcode"\s*:\s*"{re.escape(shortcode)}"'
        if re.search(xdt_pattern, html, re.DOTALL):
            log_debug("Found xdt_api block for this shortcode")
            
            # Find the specific block and extract display_url from it
            # This block contains only the target post's data
            block_start = html.find('xdt_api__v1__media__shortcode__web_info')
            if block_start != -1:
                # Look within a reasonable range after finding the block
                block_section = html[block_start:block_start + 50000]
                
                # Check if this section contains our shortcode
                if shortcode in block_section:
                    # Extract display_url values from this section only
                    display_urls = re.findall(r'"display_url"\s*:\s*"([^"]+)"', block_section)
                    
                    for url_match in display_urls:
                        decoded_url = url_match.replace('\\u0026', '&').replace('\\/', '/')
                        if decoded_url and 'cdninstagram.com' in decoded_url:
                            # Filter out profile pictures and small thumbnails
                            if '/t51.2885-19/' not in decoded_url:  # Profile pics
                                if decoded_url not in image_urls:
                                    image_urls.append(decoded_url)
                                    log_debug(f"Found image in xdt block: {decoded_url[:80]}")
        
        # Method 3: Look for carousel items specifically for this post
        # Pattern: Find carousel_media containing our shortcode
        carousel_pattern = rf'"edge_sidecar_to_children"[^}}]*"shortcode"\s*:\s*"{re.escape(shortcode)}"'
        
        if re.search(carousel_pattern, html, re.DOTALL) or 'edge_sidecar_to_children' in html:
            # Find the carousel section
            sidecar_match = re.search(r'"edge_sidecar_to_children"\s*:\s*\{[^}]*"edges"\s*:\s*\[(.*?)\]\s*\}', html, re.DOTALL)
            if sidecar_match:
                edges_content = sidecar_match.group(1)
                # Extract display_url from each edge/node
                edge_urls = re.findall(r'"display_url"\s*:\s*"([^"]+)"', edges_content)
                for url_match in edge_urls:
                    decoded_url = url_match.replace('\\u0026', '&').replace('\\/', '/')
                    if decoded_url and 'cdninstagram.com' in decoded_url:
                        if decoded_url not in image_urls:
                            image_urls.append(decoded_url)
                            log_debug(f"Found carousel image: {decoded_url[:80]}")
        
        # Method 4: If still no images, try og:image meta tag (usually has the main image)
        if not image_urls:
            og_pattern = r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']'
            og_matches = re.findall(og_pattern, html)
            for match in og_matches:
                decoded_url = match.replace('&amp;', '&')
                if decoded_url and 'cdninstagram.com' in decoded_url:
                    if decoded_url not in image_urls:
                        image_urls.append(decoded_url)
                        log_debug(f"Found og:image: {decoded_url[:80]}")
        
        # Method 5: Last resort - find display_url near our shortcode
        if not image_urls:
            # Find position of shortcode in HTML
            shortcode_pos = html.find(f'"{shortcode}"')
            if shortcode_pos != -1:
                # Look in a window around the shortcode
                window_start = max(0, shortcode_pos - 5000)
                window_end = min(len(html), shortcode_pos + 10000)
                window = html[window_start:window_end]
                
                display_urls = re.findall(r'"display_url"\s*:\s*"([^"]+)"', window)
                for url_match in display_urls[:5]:  # Limit to 5 nearby images
                    decoded_url = url_match.replace('\\u0026', '&').replace('\\/', '/')
                    if decoded_url and 'cdninstagram.com' in decoded_url:
                        if '/t51.2885-19/' not in decoded_url:  # Skip profile pics
                            if decoded_url not in image_urls:
                                image_urls.append(decoded_url)
                                log_debug(f"Found nearby image: {decoded_url[:80]}")
        
        # Extract username
        username_match = re.search(r'"username"\s*:\s*"([^"]+)"', html)
        if username_match:
            username = username_match.group(1)
        
        # Extract caption (look near shortcode)
        caption_patterns = [
            r'"edge_media_to_caption"[^}]*"text"\s*:\s*"([^"]*)"',
            r'"caption"\s*:\s*\{[^}]*"text"\s*:\s*"([^"]*)"',
            r'"accessibility_caption"\s*:\s*"([^"]*)"',
        ]
        for pattern in caption_patterns:
            caption_match = re.search(pattern, html)
            if caption_match:
                caption = caption_match.group(1)
                # Decode unicode escapes
                try:
                    caption = caption.encode().decode('unicode_escape')
                except:
                    pass
                break
        
        log_debug(f"Extracted {len(image_urls)} image(s) for post {shortcode}")
        
        # Deduplicate by base URL (same image, different params)
        if len(image_urls) > 1:
            seen_bases = set()
            unique_urls = []
            for img_url in image_urls:
                # Extract base URL without query params
                base = img_url.split('?')[0]
                # Also extract a hash from the path to identify same image
                path_match = re.search(r'/([^/]+)\.(jpg|jpeg|png|webp)', base, re.I)
                if path_match:
                    img_id = path_match.group(1)
                else:
                    img_id = base
                
                if img_id not in seen_bases:
                    seen_bases.add(img_id)
                    unique_urls.append(img_url)
            
            image_urls = unique_urls
            log_debug(f"After deduplication: {len(image_urls)} unique image(s)")
        
        return {
            'image_urls': image_urls,
            'username': username,
            'caption': caption
        }
        
    except Exception as e:
        log_debug(f"Error extracting images from page: {e}")
        import traceback
        log_debug(traceback.format_exc())
        return {
            'image_urls': [],
            'username': None,
            'caption': None
        }


def download_photo_content(url, download_path, cookies_path, info_dict=None):
    """Download photo content from Instagram."""
    Path(download_path).mkdir(parents=True, exist_ok=True)
    
    cookies_dict = parse_netscape_cookies(cookies_path)
    log_debug(f"Parsed {len(cookies_dict)} cookies from file")
    
    shortcode = extract_shortcode(url)
    log_debug(f"Extracted shortcode: {shortcode}")
    
    # Extract post data from page
    post_data = extract_post_images_from_page(url, cookies_dict, shortcode)
    image_urls = post_data.get('image_urls', [])
    username = post_data.get('username', 'instagram_user')
    caption = post_data.get('caption', '')
    
    if not image_urls:
        return None, "Could not find any image URLs for this post.", None
    
    log_debug(f"Downloading {len(image_urls)} image(s)")
    
    downloaded_files = []
    
    for idx, img_url in enumerate(image_urls[:10]):  # Max 10 images (Instagram carousel limit)
        ext = get_image_extension(img_url)
        if len(image_urls) == 1:
            filename = f"{shortcode}.{ext}"
        else:
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
        return None, "Failed to download any images.", None
    
    return downloaded_files, None, {
        'username': username,
        'caption': caption,
        'thumbnail': image_urls[0] if image_urls else ''
    }


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
    extra_info = None
    if is_photo_post or content_type == 'photo':
        media_files, error_msg, extra_info = download_photo_content(url, download_path, cookie_path, info_dict)
    else:
        # Try video download first
        media_files, error_msg = download_video_content(url, download_path, cookie_path, ytdlp_bin, content_type)
        
        # If video download fails with "no video formats", try photo download
        if error_msg and is_photo_only_error(error_msg):
            log_debug("Video download failed, trying as photo...")
            is_photo_post = True
            content_type = get_content_type(url, info_dict, True)
            media_files, error_msg, extra_info = download_photo_content(url, download_path, cookie_path, info_dict)
    
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
    
    # Override with extra_info from photo download if available
    if extra_info:
        if extra_info.get('username'):
            username = extra_info['username']
        if extra_info.get('caption'):
            caption = extra_info['caption']
        if extra_info.get('thumbnail'):
            thumbnail = extra_info['thumbnail']
    
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
    
    # Update content type based on actual downloaded items
    if len(items) > 1:
        content_type = 'carousel'
    elif is_photo_post and len(items) == 1:
        content_type = 'photo'
    
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