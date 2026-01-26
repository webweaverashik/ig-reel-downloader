#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
IGReelDownloader.net

Supports: Reels, Videos, Photos, Stories, Carousel posts.
Uses yt-dlp for video content, direct HTTP for photos.

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_json> [yt_dlp_path]
"""

import sys
import os
import json
import subprocess
import re
import hashlib
from pathlib import Path

# Try to import requests, fall back to urllib if not available
try:
    import requests
    HAS_REQUESTS = True
except ImportError:
    import urllib.request
    import urllib.error
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


def run_command(cmd, timeout=120):
    """Run a shell command and return output."""
    log_debug(f"Running command: {cmd[:200]}...")
    
    try:
        # Set up environment - mimic terminal environment exactly
        env = os.environ.copy()
        env['HOME'] = '/tmp'
        # Prepend common binary paths to ensure yt-dlp is found
        env['PATH'] = '/usr/local/bin:/usr/bin:/bin:/home/ubuntu/.local/bin:' + env.get('PATH', '')
        # Disable Python buffering for real-time output
        env['PYTHONUNBUFFERED'] = '1'
        
        script_dir = os.path.dirname(os.path.abspath(__file__))
        
        result = subprocess.run(
            cmd,
            shell=True,
            capture_output=True,
            text=True,
            timeout=timeout,
            env=env,
            cwd=script_dir
        )
        
        return result.returncode, result.stdout, result.stderr
    except subprocess.TimeoutExpired:
        return -1, '', 'Command timed out'
    except Exception as e:
        return -1, '', str(e)


def get_ytdlp_command():
    """Get the yt-dlp command that works."""
    import shutil
    
    # First, try to find yt-dlp using which
    ytdlp_path = shutil.which('yt-dlp')
    if ytdlp_path:
        code, stdout, stderr = run_command(f'"{ytdlp_path}" --version', timeout=10)
        if code == 0 and stdout.strip():
            log_debug(f"Found yt-dlp via which: {ytdlp_path} (version: {stdout.strip()})")
            return ytdlp_path
    
    # Try common paths
    common_paths = [
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
    ]
    
    for path in common_paths:
        if os.path.isfile(path) and os.access(path, os.X_OK):
            code, stdout, stderr = run_command(f'"{path}" --version', timeout=10)
            if code == 0 and stdout.strip():
                log_debug(f"Found yt-dlp at: {path} (version: {stdout.strip()})")
                return path
    
    # Try just 'yt-dlp' command
    code, stdout, stderr = run_command('yt-dlp --version', timeout=10)
    if code == 0 and stdout.strip():
        log_debug(f"Found yt-dlp in PATH (version: {stdout.strip()})")
        return 'yt-dlp'
    
    log_debug("Warning: Could not verify yt-dlp, using default")
    return 'yt-dlp'


def fetch_with_ytdlp(url, download_path, cookie_file):
    """Fetch content using yt-dlp - mimics terminal command exactly."""
    
    ytdlp_cmd = get_ytdlp_command()
    
    # Build command exactly like terminal
    # yt-dlp --cookies cookies/instagram.txt --dump-json "URL"
    metadata_cmd = f'{ytdlp_cmd} --cookies "{cookie_file}" --dump-json --no-warnings --no-check-certificates "{url}"'
    
    log_debug(f"Fetching metadata...")
    code, stdout, stderr = run_command(metadata_cmd, timeout=60)
    
    if code != 0:
        error_text = (stderr + '\n' + stdout).strip()
        log_debug(f"Metadata fetch error: {error_text[:300]}")
        return None, error_text
    
    # Parse JSON output
    info_dict = None
    entries = []
    
    for line in stdout.strip().split('\n'):
        line = line.strip()
        if line:
            try:
                entry = json.loads(line)
                entries.append(entry)
            except json.JSONDecodeError:
                continue
    
    if not entries:
        return None, "No metadata found"
    
    info_dict = entries[0].copy()
    if len(entries) > 1:
        info_dict['entries'] = entries
        info_dict['_type'] = 'playlist'
    
    log_debug(f"Metadata fetched successfully")
    
    # Now download
    Path(download_path).mkdir(parents=True, exist_ok=True)
    output_template = os.path.join(download_path, '%(id)s.%(ext)s')
    
    download_cmd = f'{ytdlp_cmd} --cookies "{cookie_file}" --no-warnings --no-check-certificates -o "{output_template}" --merge-output-format mp4 --write-thumbnail --convert-thumbnails jpg "{url}"'
    
    log_debug(f"Downloading media...")
    code, stdout, stderr = run_command(download_cmd, timeout=300)
    
    # Check for downloaded files even if there's an error
    downloaded_files = []
    if os.path.isdir(download_path):
        for f in Path(download_path).glob('*'):
            if f.is_file() and f.suffix.lower() in ['.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp']:
                downloaded_files.append(f)
    
    if downloaded_files:
        log_debug(f"Downloaded {len(downloaded_files)} file(s)")
        return {
            'info_dict': info_dict,
            'files': sorted(downloaded_files)
        }, None
    
    error_text = (stderr + '\n' + stdout).strip()
    return None, error_text


def parse_netscape_cookies(cookie_file):
    """Parse Netscape format cookie file."""
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


def fetch_with_requests(url, download_path, cookie_file):
    """Fetch content using direct HTTP requests - for photos."""
    
    if not HAS_REQUESTS:
        return None, "requests library not installed"
    
    cookies_dict = parse_netscape_cookies(cookie_file)
    if not cookies_dict:
        return None, "Could not parse cookies"
    
    log_debug(f"Fetching page with requests...")
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
    }
    
    try:
        session = requests.Session()
        session.cookies.update(cookies_dict)
        response = session.get(url, headers=headers, timeout=30)
        html = response.text
    except Exception as e:
        return None, f"Failed to fetch page: {str(e)}"
    
    log_debug(f"Page fetched, length: {len(html)}")
    
    shortcode = extract_shortcode(url)
    
    # Check if it's video content
    is_video = '/reel/' in url.lower() or '/reels/' in url.lower() or '/tv/' in url.lower()
    has_video_in_html = '"video_url"' in html or '"is_video":true' in html
    
    Path(download_path).mkdir(parents=True, exist_ok=True)
    downloaded_files = []
    
    # Try to get video URL if it's video content
    if is_video or has_video_in_html:
        video_urls = []
        
        # Extract video URLs from HTML
        patterns = [
            r'"video_url"\s*:\s*"([^"]+)"',
            r'"contentUrl"\s*:\s*"([^"]+\.mp4[^"]*)"',
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, html)
            for match in matches:
                decoded = match.replace('\\u0026', '&').replace('\\/', '/')
                if 'cdninstagram.com' in decoded:
                    video_urls.append(decoded)
        
        if video_urls:
            video_url = video_urls[0]
            filename = f"{shortcode}.mp4"
            filepath = os.path.join(download_path, filename)
            
            log_debug(f"Downloading video: {video_url[:60]}...")
            
            try:
                vid_response = session.get(video_url, headers={
                    **headers,
                    'Referer': 'https://www.instagram.com/'
                }, timeout=60, stream=True)
                vid_response.raise_for_status()
                
                with open(filepath, 'wb') as f:
                    for chunk in vid_response.iter_content(chunk_size=8192):
                        if chunk:
                            f.write(chunk)
                
                if os.path.exists(filepath) and os.path.getsize(filepath) > 10000:
                    downloaded_files.append(Path(filepath))
                    log_debug(f"Video downloaded: {filename}")
            except Exception as e:
                log_debug(f"Video download failed: {e}")
    
    # If no video, try images
    if not downloaded_files:
        image_urls = []
        
        # Extract image URLs
        patterns = [
            r'"display_url"\s*:\s*"([^"]+)"',
            r'"src"\s*:\s*"([^"]+cdninstagram\.com[^"]+)"',
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, html)
            for match in matches:
                decoded = match.replace('\\u0026', '&').replace('\\/', '/')
                if 'cdninstagram.com' in decoded and '/t51.2885-19/' not in decoded:
                    if decoded not in image_urls:
                        image_urls.append(decoded)
        
        # Also try og:image
        og_match = re.search(r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']', html, re.I)
        if og_match:
            og_url = og_match.group(1).replace('&amp;', '&')
            if og_url not in image_urls:
                image_urls.insert(0, og_url)
        
        # Deduplicate by image ID
        unique_urls = []
        seen_ids = set()
        for img_url in image_urls:
            id_match = re.search(r'/(\d+_\d+_\d+_\w+)\.(jpg|jpeg|png|webp)', img_url, re.I)
            img_id = id_match.group(1) if id_match else img_url.split('/')[-1].split('?')[0]
            if img_id not in seen_ids:
                seen_ids.add(img_id)
                unique_urls.append(img_url)
        
        image_urls = unique_urls[:10]  # Limit to 10
        
        log_debug(f"Found {len(image_urls)} unique image(s)")
        
        for idx, img_url in enumerate(image_urls):
            ext = 'jpg'
            if '.png' in img_url.lower():
                ext = 'png'
            elif '.webp' in img_url.lower():
                ext = 'webp'
            
            if len(image_urls) == 1:
                filename = f"{shortcode}.{ext}"
            else:
                filename = f"{shortcode}_{idx + 1:02d}.{ext}"
            
            filepath = os.path.join(download_path, filename)
            
            try:
                img_response = session.get(img_url, headers={
                    **headers,
                    'Referer': 'https://www.instagram.com/'
                }, timeout=30)
                img_response.raise_for_status()
                
                with open(filepath, 'wb') as f:
                    f.write(img_response.content)
                
                if os.path.exists(filepath) and os.path.getsize(filepath) > 1000:
                    downloaded_files.append(Path(filepath))
                    log_debug(f"Image downloaded: {filename}")
            except Exception as e:
                log_debug(f"Image download failed: {e}")
    
    if not downloaded_files:
        return None, "No media found or downloaded"
    
    # Extract metadata
    username = None
    caption = ''
    thumbnail = ''
    
    # Extract username
    username_patterns = [
        r'"owner"\s*:\s*\{[^}]*"username"\s*:\s*"([^"]+)"',
        r'"user"\s*:\s*\{[^}]*"username"\s*:\s*"([^"]+)"',
        r'<meta\s+name=["\']author["\']\s+content=["\']@?([^"\']+)["\']',
    ]
    for pattern in username_patterns:
        match = re.search(pattern, html, re.I)
        if match:
            username = match.group(1)
            break
    
    # Extract caption
    caption_match = re.search(r'"caption"\s*:\s*\{[^}]*"text"\s*:\s*"([^"]*)"', html)
    if caption_match:
        caption = caption_match.group(1)
    
    # Extract thumbnail
    if og_match:
        thumbnail = og_match.group(1).replace('&amp;', '&')
    
    return {
        'files': downloaded_files,
        'username': username or 'instagram_user',
        'caption': caption,
        'thumbnail': thumbnail,
        'is_carousel': len(downloaded_files) > 1
    }, None


def main():
    if len(sys.argv) < 4:
        log_error("Usage: python instagram_fetch.py <url> <download_path> <cookies_json> [yt_dlp_path]", "invalid_args")

    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_json = sys.argv[3]

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

    # Determine content type from URL
    url_lower = url.lower()
    is_video_url = '/reel/' in url_lower or '/reels/' in url_lower or '/tv/' in url_lower
    is_story_url = '/stories/' in url_lower
    
    last_error = None
    cookies_tried = 0
    all_errors = []

    for cookie_file in cookie_files:
        cookies_tried += 1
        cookie_name = os.path.basename(cookie_file)
        
        log_debug(f"Trying cookie #{cookies_tried}: {cookie_name}")
        
        # Verify cookie file exists
        if not os.path.isfile(cookie_file):
            log_debug(f"Cookie file not found: {cookie_file}")
            all_errors.append({"cookie": cookie_name, "error": "File not found"})
            continue
        
        result = None
        error = None
        
        # For videos/reels, try yt-dlp first
        if is_video_url or is_story_url:
            result, error = fetch_with_ytdlp(url, download_path, cookie_file)
            
            if error and 'No video formats' in error:
                # It's actually a photo, try requests
                log_debug("yt-dlp says no video, trying requests...")
                result, error = fetch_with_requests(url, download_path, cookie_file)
        
        # For photos/posts, use requests directly
        else:
            result, error = fetch_with_requests(url, download_path, cookie_file)
            
            # If requests failed but might be a video, try yt-dlp
            if error and HAS_REQUESTS:
                log_debug("Requests failed, trying yt-dlp...")
                result, error = fetch_with_ytdlp(url, download_path, cookie_file)
        
        if result:
            # Success! Build response
            files = result.get('files', [])
            info_dict = result.get('info_dict', {})
            
            # Determine content type
            has_video = any(f.suffix.lower() in ['.mp4', '.webm', '.mkv'] for f in files)
            
            if '/reel/' in url_lower or '/reels/' in url_lower:
                content_type = 'reel'
            elif '/stories/' in url_lower:
                content_type = 'story'
            elif '/tv/' in url_lower:
                content_type = 'video'
            elif has_video:
                content_type = 'video'
            elif len(files) > 1:
                content_type = 'carousel'
            else:
                content_type = 'photo'
            
            # Get metadata
            username = result.get('username') or info_dict.get('channel') or info_dict.get('uploader') or 'instagram_user'
            caption = result.get('caption') or info_dict.get('description', '')
            thumbnail = result.get('thumbnail') or info_dict.get('thumbnail', '')
            
            # Build items array
            items = []
            for i, file_path in enumerate(files):
                ext = file_path.suffix.lower().lstrip('.')
                is_video_file = ext in ['mp4', 'webm', 'mkv']
                
                # Find thumbnail
                thumb_path = None
                if is_video_file:
                    for thumb in Path(download_path).glob('*.jpg'):
                        if file_path.stem.split('_')[0] in thumb.stem:
                            thumb_path = str(thumb)
                            break
                
                items.append({
                    "id": i + 1,
                    "type": "video" if is_video_file else "image",
                    "format": ext,
                    "quality": "HD" if is_video_file else "Original",
                    "path": str(file_path),
                    "filename": file_path.name,
                    "thumbnail": thumbnail,
                    "thumbnail_file": thumb_path or ""
                })
            
            response = {
                "success": True,
                "type": content_type,
                "username": username,
                "caption": (caption[:500] if caption else ""),
                "thumbnail": thumbnail,
                "items": items,
                "cookies_tried": cookies_tried,
                "cookie_used": cookie_name
            }
            
            print(json.dumps(response))
            sys.exit(0)
        
        # Store error
        last_error = error
        all_errors.append({"cookie": cookie_name, "error": (error or "Unknown")[:200]})
        log_debug(f"Cookie #{cookies_tried} failed: {(error or 'Unknown')[:100]}")

    # All cookies failed
    debug_info = {
        "cookies_tried": cookies_tried,
        "all_errors": all_errors,
        "has_requests": HAS_REQUESTS
    }
    
    log_error(
        f"All {cookies_tried} cookie(s) failed. Please check cookie files and try again.",
        "all_cookies_failed",
        cookies_tried,
        debug_info
    )


if __name__ == "__main__":
    main()