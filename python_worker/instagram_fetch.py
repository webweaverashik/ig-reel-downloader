#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
IGReelDownloader.net

Supports: Reels, Videos, Photos, Stories, Carousel posts.
- Uses yt-dlp for video content (as module or binary)
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


def find_ytdlp_command(ytdlp_input):
    """
    Find a working yt-dlp command.
    Returns a list of command parts (e.g., ['yt-dlp'] or ['python3', '-m', 'yt_dlp'])
    """
    python_bin = sys.executable or 'python3'
    
    # First, check if yt-dlp is installed as a Python module
    try:
        result = subprocess.run(
            [python_bin, '-m', 'yt_dlp', '--version'],
            capture_output=True,
            text=True,
            timeout=10,
            env=get_env()
        )
        if result.returncode == 0:
            log_debug(f"Using yt-dlp as Python module: {python_bin} -m yt_dlp (version: {result.stdout.strip()})")
            return [python_bin, '-m', 'yt_dlp']
    except Exception as e:
        log_debug(f"Module check failed: {e}")
    
    # Check if the provided path is a directory (Python package)
    if ytdlp_input and os.path.isdir(ytdlp_input):
        log_debug(f"yt-dlp path is a directory: {ytdlp_input}, using as module")
        return [python_bin, '-m', 'yt_dlp']
    
    # Try to find yt-dlp as executable
    candidates = []
    if ytdlp_input and ytdlp_input.strip():
        candidates.append(ytdlp_input.strip())
    
    # Check which yt-dlp
    which_result = shutil.which('yt-dlp')
    if which_result:
        candidates.append(which_result)
    
    candidates.extend([
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
        '/root/.local/bin/yt-dlp',
        'yt-dlp',
    ])
    
    seen = set()
    for candidate in candidates:
        if not candidate or candidate in seen:
            continue
        seen.add(candidate)
        
        # Skip if it's a directory
        if os.path.isdir(candidate):
            log_debug(f"Skipping directory: {candidate}")
            continue
        
        try:
            # Check if it's an executable file
            if os.path.isfile(candidate) and os.access(candidate, os.X_OK):
                result = subprocess.run(
                    [candidate, '--version'],
                    capture_output=True,
                    text=True,
                    timeout=10,
                    env=get_env()
                )
                if result.returncode == 0:
                    log_debug(f"Found working yt-dlp binary: {candidate} (version: {result.stdout.strip()})")
                    return [candidate]
        except Exception as e:
            log_debug(f"Binary check failed for {candidate}: {e}")
            continue
    
    # Last resort: try running 'yt-dlp' command directly
    try:
        result = subprocess.run(
            ['yt-dlp', '--version'],
            capture_output=True,
            text=True,
            timeout=10,
            env=get_env()
        )
        if result.returncode == 0:
            log_debug(f"Using 'yt-dlp' from PATH (version: {result.stdout.strip()})")
            return ['yt-dlp']
    except Exception:
        pass
    
    # Fallback to module approach
    log_debug("Falling back to Python module approach")
    return [python_bin, '-m', 'yt_dlp']


def run_ytdlp(ytdlp_cmd, args, timeout=120):
    """Run yt-dlp with proper error handling."""
    full_cmd = ytdlp_cmd + args
    log_debug(f"Running: {' '.join(full_cmd[:5])}...")
    
    try:
        result = subprocess.run(
            full_cmd,
            capture_output=True,
            text=True,
            timeout=timeout,
            env=get_env()
        )
        return result.returncode, result.stdout, result.stderr
    except subprocess.TimeoutExpired:
        return -2, '', 'Request timed out'
    except FileNotFoundError as e:
        return -3, '', f'Command not found: {str(e)}'
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


def extract_username_from_url(url):
    """Extract username from story URL if present."""
    match = re.search(r'/stories/([^/]+)/', url)
    if match:
        return match.group(1)
    return None


def find_carousel_media(html, shortcode):
    """
    Find carousel media items from Instagram HTML.
    Returns list of image URLs.
    """
    image_urls = []
    
    log_debug(f"Searching for carousel media with shortcode: {shortcode}")
    
    # Method 1: Look for edge_sidecar_to_children (carousel indicator)
    sidecar_pattern = r'"edge_sidecar_to_children"\s*:\s*\{\s*"edges"\s*:\s*\[(.*?)\]\s*\}'
    sidecar_match = re.search(sidecar_pattern, html, re.DOTALL)
    
    if sidecar_match:
        log_debug("Found edge_sidecar_to_children (carousel)")
        edges_content = sidecar_match.group(1)
        
        # Extract each node's display_url
        node_pattern = r'\{\s*"node"\s*:\s*\{[^}]*"display_url"\s*:\s*"([^"]+)"[^}]*\}'
        node_urls = re.findall(node_pattern, edges_content, re.DOTALL)
        
        for url in node_urls:
            decoded_url = url.replace('\\u0026', '&').replace('\\/', '/')
            if decoded_url and 'cdninstagram.com' in decoded_url:
                if decoded_url not in image_urls:
                    image_urls.append(decoded_url)
                    log_debug(f"Found carousel item: {decoded_url[:60]}...")
    
    # Method 2: Look for carousel_media array
    carousel_pattern = r'"carousel_media"\s*:\s*\[(.*?)\]'
    carousel_match = re.search(carousel_pattern, html, re.DOTALL)
    
    if carousel_match and not image_urls:
        log_debug("Found carousel_media array")
        carousel_content = carousel_match.group(1)
        
        # Extract image_versions2 -> candidates -> url
        url_pattern = r'"url"\s*:\s*"([^"]+)"'
        urls = re.findall(url_pattern, carousel_content)
        
        seen_bases = set()
        for url in urls:
            decoded_url = url.replace('\\u0026', '&').replace('\\/', '/')
            if decoded_url and 'cdninstagram.com' in decoded_url:
                # Get base URL without size params to dedupe
                base = re.sub(r'/s\d+x\d+/', '/s1080x1080/', decoded_url.split('?')[0])
                if base not in seen_bases:
                    seen_bases.add(base)
                    # Prefer highest quality version
                    if '/s1080x1080/' in decoded_url or '/s1440x1440/' in decoded_url or len(seen_bases) <= 10:
                        if decoded_url not in image_urls:
                            image_urls.append(decoded_url)
                            log_debug(f"Found carousel_media item: {decoded_url[:60]}...")
    
    # Method 3: Look for items array with image data
    items_pattern = r'"items"\s*:\s*\[\s*\{(.*?)\}\s*\]'
    items_match = re.search(items_pattern, html, re.DOTALL)
    
    if items_match and not image_urls:
        log_debug("Found items array")
        items_content = items_match.group(1)
        
        # Look for display_url or image URLs
        url_pattern = r'"(?:display_url|url)"\s*:\s*"([^"]+cdninstagram\.com[^"]+)"'
        urls = re.findall(url_pattern, items_content)
        
        for url in urls:
            decoded_url = url.replace('\\u0026', '&').replace('\\/', '/')
            if decoded_url not in image_urls:
                image_urls.append(decoded_url)
                log_debug(f"Found items array image: {decoded_url[:60]}...")
    
    return image_urls


def extract_post_info_from_html(html, shortcode):
    """
    Extract post information (username, caption, thumbnail) from HTML.
    Returns dict with username, caption, thumbnail.
    """
    info = {
        'username': None,
        'caption': None,
        'thumbnail': None
    }
    
    # Method 1: Look for owner username near the shortcode
    # Find the media block containing this shortcode
    shortcode_pattern = rf'"shortcode"\s*:\s*"{re.escape(shortcode)}"'
    shortcode_pos = -1
    
    match = re.search(shortcode_pattern, html)
    if match:
        shortcode_pos = match.start()
    
    if shortcode_pos != -1:
        # Search in a window around the shortcode for the owner
        window_start = max(0, shortcode_pos - 5000)
        window_end = min(len(html), shortcode_pos + 5000)
        window = html[window_start:window_end]
        
        # Look for owner block with username
        owner_patterns = [
            r'"owner"\s*:\s*\{[^}]*"username"\s*:\s*"([^"]+)"',
            r'"user"\s*:\s*\{[^}]*"username"\s*:\s*"([^"]+)"',
        ]
        
        for pattern in owner_patterns:
            username_match = re.search(pattern, window)
            if username_match:
                info['username'] = username_match.group(1)
                log_debug(f"Found username from owner block: {info['username']}")
                break
    
    # Method 2: Look for author meta tag
    if not info['username']:
        author_pattern = r'<meta\s+name=["\']author["\']\s+content=["\']@?([^"\']+)["\']'
        author_match = re.search(author_pattern, html, re.I)
        if author_match:
            info['username'] = author_match.group(1)
            log_debug(f"Found username from meta author: {info['username']}")
    
    # Method 3: Look for instapp:owner_user_id and then find username
    if not info['username']:
        # Try to find username in og:description or other meta tags
        desc_pattern = r'<meta\s+(?:property|name)=["\']og:description["\']\s+content=["\']([^"\']+)["\']'
        desc_match = re.search(desc_pattern, html, re.I)
        if desc_match:
            desc = desc_match.group(1)
            # Description often starts with "X likes, Y comments - username"
            # Or "username on Instagram: ..."
            username_from_desc = re.search(r'^([^:\s]+)\s+on\s+Instagram', desc)
            if username_from_desc:
                info['username'] = username_from_desc.group(1)
                log_debug(f"Found username from og:description: {info['username']}")
    
    # Method 4: Look in title tag
    if not info['username']:
        title_pattern = r'<title>([^<]+)</title>'
        title_match = re.search(title_pattern, html, re.I)
        if title_match:
            title = title_match.group(1)
            # Title often contains: "username on Instagram: ..." or "@username ..."
            username_from_title = re.search(r'[@]?([a-zA-Z0-9._]+)\s+(?:on\s+Instagram|•)', title)
            if username_from_title:
                info['username'] = username_from_title.group(1)
                log_debug(f"Found username from title: {info['username']}")
    
    # Extract caption
    caption_patterns = [
        r'"edge_media_to_caption"\s*:\s*\{\s*"edges"\s*:\s*\[\s*\{\s*"node"\s*:\s*\{\s*"text"\s*:\s*"([^"]*)"',
        r'"caption"\s*:\s*\{\s*"text"\s*:\s*"([^"]*)"',
        r'"accessibility_caption"\s*:\s*"([^"]*)"',
    ]
    for pattern in caption_patterns:
        caption_match = re.search(pattern, html)
        if caption_match:
            info['caption'] = caption_match.group(1)
            try:
                info['caption'] = info['caption'].encode().decode('unicode_escape')
            except:
                pass
            break
    
    # Extract thumbnail from og:image
    og_image_pattern = r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']'
    og_match = re.search(og_image_pattern, html, re.I)
    if og_match:
        info['thumbnail'] = og_match.group(1).replace('&amp;', '&')
    
    return info


def extract_post_images_from_page(url, cookies_dict, shortcode):
    """
    Extract image URLs specifically for the target post.
    Handles both single images and carousels.
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
        is_carousel = False
        
        # Extract post info (username, caption, thumbnail)
        post_info = extract_post_info_from_html(html, shortcode)
        
        # Check if this is a carousel post
        if '"edge_sidecar_to_children"' in html or '"carousel_media"' in html or '"GraphSidecar"' in html:
            is_carousel = True
            log_debug("Detected CAROUSEL post")
            image_urls = find_carousel_media(html, shortcode)
        
        # If not carousel or carousel extraction failed, try single image extraction
        if not image_urls:
            log_debug("Trying single image extraction")
            
            # Method 1: Find display_url in the media data for this shortcode
            shortcode_pattern = rf'"shortcode"\s*:\s*"{re.escape(shortcode)}"'
            if re.search(shortcode_pattern, html):
                log_debug("Found shortcode in HTML")
                
                # Find the media block containing this shortcode
                shortcode_pos = html.find(f'"{shortcode}"')
                if shortcode_pos != -1:
                    # Search in a window around the shortcode
                    window_start = max(0, shortcode_pos - 10000)
                    window_end = min(len(html), shortcode_pos + 10000)
                    window = html[window_start:window_end]
                    
                    # Find display_url in this window
                    display_url_pattern = r'"display_url"\s*:\s*"([^"]+)"'
                    display_urls = re.findall(display_url_pattern, window)
                    
                    for url_match in display_urls:
                        decoded_url = url_match.replace('\\u0026', '&').replace('\\/', '/')
                        if decoded_url and 'cdninstagram.com' in decoded_url:
                            # Skip profile pictures
                            if '/t51.2885-19/' not in decoded_url:
                                if decoded_url not in image_urls:
                                    image_urls.append(decoded_url)
                                    log_debug(f"Found display_url: {decoded_url[:60]}...")
                                    # For single image, we only need one
                                    if not is_carousel:
                                        break
            
            # Method 2: Try og:image meta tag (single main image)
            if not image_urls:
                og_pattern = r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']'
                og_match = re.search(og_pattern, html)
                if og_match:
                    decoded_url = og_match.group(1).replace('&amp;', '&')
                    if decoded_url and 'cdninstagram.com' in decoded_url:
                        image_urls.append(decoded_url)
                        log_debug(f"Found og:image: {decoded_url[:60]}...")
            
            # Method 3: Look for image_versions2 candidates
            if not image_urls:
                candidates_pattern = r'"image_versions2"\s*:\s*\{\s*"candidates"\s*:\s*\[(.*?)\]'
                candidates_match = re.search(candidates_pattern, html, re.DOTALL)
                if candidates_match:
                    candidates_content = candidates_match.group(1)
                    url_pattern = r'"url"\s*:\s*"([^"]+)"'
                    urls = re.findall(url_pattern, candidates_content)
                    
                    # Get highest resolution (first one is usually largest)
                    if urls:
                        decoded_url = urls[0].replace('\\u0026', '&').replace('\\/', '/')
                        if decoded_url and 'cdninstagram.com' in decoded_url:
                            image_urls.append(decoded_url)
                            log_debug(f"Found image_versions2 candidate: {decoded_url[:60]}...")
        
        # Deduplicate images by base URL
        if len(image_urls) > 1:
            seen_bases = set()
            unique_urls = []
            for img_url in image_urls:
                # Extract a unique identifier from the URL
                id_match = re.search(r'/(\d+_\d+_\d+_\w+)\.(jpg|jpeg|png|webp)', img_url, re.I)
                if id_match:
                    img_id = id_match.group(1)
                else:
                    # Fallback: use the filename part
                    img_id = img_url.split('/')[-1].split('?')[0]
                
                if img_id not in seen_bases:
                    seen_bases.add(img_id)
                    unique_urls.append(img_url)
            
            image_urls = unique_urls
        
        log_debug(f"Final result: {len(image_urls)} image(s), is_carousel={is_carousel}")
        
        return {
            'image_urls': image_urls,
            'username': post_info['username'],
            'caption': post_info['caption'],
            'thumbnail': post_info['thumbnail'],
            'is_carousel': is_carousel or len(image_urls) > 1
        }
        
    except Exception as e:
        log_debug(f"Error extracting images from page: {e}")
        import traceback
        log_debug(traceback.format_exc())
        return {
            'image_urls': [],
            'username': None,
            'caption': None,
            'thumbnail': None,
            'is_carousel': False
        }


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


def is_ytdlp_execution_error(error_text):
    """Check if error is a yt-dlp execution/crash error (not content-related)."""
    if not error_text:
        return False
    error_lower = error_text.lower()
    execution_indicators = [
        'traceback',
        'modulenotfounderror',
        'importerror',
        'syntaxerror',
        'nameerror',
        'typeerror',
        'attributeerror',
        'filenotfounderror',
        '__main__',
        'frozen runpy',
        '_run_module_as_main',
    ]
    return any(indicator in error_lower for indicator in execution_indicators)


def is_permanent_content_error(error_text):
    """Check if error is a permanent content error (not retryable)."""
    if not error_text:
        return False
    
    # First, make sure it's NOT a yt-dlp execution error
    if is_ytdlp_execution_error(error_text):
        return False
    
    error_lower = error_text.lower()
    permanent_keywords = [
        'private',
        'does not exist',
        'unavailable',
        'blocked',
        'this content isn\'t available',
        'page not found',
        'sorry, this page',
    ]
    return any(keyword in error_lower for keyword in permanent_keywords)


def is_not_found_error(error_text):
    """Check if error indicates content was not found/removed."""
    if not error_text:
        return False
    
    # First, make sure it's NOT a yt-dlp execution error
    if is_ytdlp_execution_error(error_text):
        return False
    
    error_lower = error_text.lower()
    not_found_keywords = [
        'not found',
        '404',
        'removed',
        'deleted',
        'no longer available',
    ]
    return any(keyword in error_lower for keyword in not_found_keywords)


def is_cookie_error(error_text):
    """Check if error is related to cookies/authentication."""
    if not error_text:
        return False
    
    # First, make sure it's NOT a yt-dlp execution error
    if is_ytdlp_execution_error(error_text):
        return False
    
    error_lower = error_text.lower()
    cookie_keywords = [
        'login required',
        'login_required',
        'please log in',
        'authentication required',
        'session expired',
        'invalid session',
        'cookie',
        'sessionid',
    ]
    return any(keyword in error_lower for keyword in cookie_keywords)


def get_content_type(url, info_dict=None, is_photo=False, is_carousel=False):
    """Determine content type from URL and metadata."""
    url_lower = url.lower()
    
    if '/reel/' in url_lower or '/reels/' in url_lower:
        return 'reel'
    if '/stories/' in url_lower:
        return 'story'
    if '/tv/' in url_lower:
        return 'video'
    
    if is_carousel:
        return 'carousel'
    
    if is_photo:
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


def extract_username_from_ytdlp(info_dict, url):
    """Extract the correct username from yt-dlp info dict."""
    if not info_dict:
        return None
    
    # Priority order for username extraction:
    # 1. channel (usually the post author)
    # 2. uploader (might be the logged-in user sometimes)
    # 3. uploader_id
    
    username = None
    
    # Try channel first (most reliable for post author)
    if info_dict.get('channel'):
        username = info_dict['channel']
    elif info_dict.get('uploader'):
        username = info_dict['uploader']
    elif info_dict.get('uploader_id'):
        username = info_dict['uploader_id']
    
    # If still no username, try to extract from URL for stories
    if not username:
        username = extract_username_from_url(url)
    
    # Clean up username (remove @ if present)
    if username and username.startswith('@'):
        username = username[1:]
    
    return username


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
    thumbnail = post_data.get('thumbnail', '')
    is_carousel = post_data.get('is_carousel', False)
    
    if not image_urls:
        return None, "Could not find any image URLs for this post.", None
    
    log_debug(f"Downloading {len(image_urls)} image(s), is_carousel={is_carousel}")
    
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
        'thumbnail': thumbnail or (image_urls[0] if image_urls else ''),
        'is_carousel': is_carousel
    }


def fetch_metadata(url, cookies_path, ytdlp_cmd):
    """Fetch metadata using yt-dlp --dump-json."""
    args = [
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
    return_code, stdout, stderr = run_ytdlp(ytdlp_cmd, args, timeout=60)

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


def download_video_content(url, download_path, cookies_path, ytdlp_cmd, content_type='video'):
    """Download video content using yt-dlp."""
    Path(download_path).mkdir(parents=True, exist_ok=True)
    output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')

    args = [
        '--cookies', cookies_path,
        '--no-warnings',
        '--no-check-certificates',
        '--no-playlist-reverse',
        '--socket-timeout', '30',
        '-o', output_template,
        '--merge-output-format', 'mp4',
        '--write-thumbnail',
        '--convert-thumbnails', 'jpg',
        '--extractor-args', 'instagram:api_only=false',
        url
    ]

    log_debug(f"Downloading video to: {download_path}")
    return_code, stdout, stderr = run_ytdlp(ytdlp_cmd, args, timeout=300)
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


def try_download(url, download_path, cookie_path, ytdlp_cmd, cookie_index):
    """Try to fetch and download with a specific cookie file."""
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
    info_dict, error_msg = fetch_metadata(url, cookie_path, ytdlp_cmd)
    
    is_photo_post = False
    is_carousel = False
    extra_info = None
    
    if error_msg:
        log_debug(f"Metadata fetch error: {error_msg[:200]}")
        
        # Check if it's a yt-dlp execution error (should retry with different approach)
        if is_ytdlp_execution_error(error_msg):
            log_debug("Detected yt-dlp execution error, this is a system issue not content issue")
            return None, error_msg, "ytdlp_error", True
        
        # Check if it's a "no video formats" error - means it's a photo post
        if is_photo_only_error(error_msg):
            log_debug("Detected photo post (no video formats), switching to photo download...")
            is_photo_post = True
            info_dict = None
        elif is_permanent_content_error(error_msg):
            return None, "This content is from a private account or is not available.", "private_content", False
        elif is_not_found_error(error_msg):
            return None, "This post was not found or has been removed.", "not_found", False
        elif is_cookie_error(error_msg):
            return None, error_msg, "cookie_error", True
        else:
            # Unknown error - try as photo before giving up
            log_debug(f"Unknown error, trying as photo...")
            is_photo_post = True
            info_dict = None
    
    content_type = get_content_type(url, info_dict, is_photo_post, is_carousel)
    log_debug(f"Content type: {content_type}, is_photo: {is_photo_post}")
    
    # Download based on content type
    if is_photo_post or content_type == 'photo':
        media_files, error_msg, extra_info = download_photo_content(url, download_path, cookie_path, info_dict)
        if extra_info:
            is_carousel = extra_info.get('is_carousel', False)
    else:
        # Try video download first
        media_files, error_msg = download_video_content(url, download_path, cookie_path, ytdlp_cmd, content_type)
        
        # If video download fails with "no video formats", try photo download
        if error_msg and is_photo_only_error(error_msg):
            log_debug("Video download failed, trying as photo...")
            is_photo_post = True
            media_files, error_msg, extra_info = download_photo_content(url, download_path, cookie_path, info_dict)
            if extra_info:
                is_carousel = extra_info.get('is_carousel', False)
        elif error_msg and is_ytdlp_execution_error(error_msg):
            log_debug("yt-dlp execution error during download, trying photo fallback...")
            media_files, error_msg, extra_info = download_photo_content(url, download_path, cookie_path, info_dict)
            if extra_info:
                is_carousel = extra_info.get('is_carousel', False)
    
    if error_msg:
        return None, error_msg, "download_error", True
    
    if not media_files:
        return None, "No media files downloaded.", "no_media", True
    
    # Update content type based on results
    content_type = get_content_type(url, info_dict, is_photo_post, is_carousel)
    
    # Build response
    username = None
    caption = ''
    thumbnail = ''
    
    # Get username from yt-dlp info (with proper extraction)
    if info_dict:
        username = extract_username_from_ytdlp(info_dict, url)
        caption = info_dict.get('description', info_dict.get('title', ''))
        thumbnail = info_dict.get('thumbnail', '')
    
    # Override with extra_info from photo download if available (more accurate for photos)
    if extra_info:
        if extra_info.get('username'):
            username = extra_info['username']
        if extra_info.get('caption'):
            caption = extra_info['caption']
        if extra_info.get('thumbnail'):
            thumbnail = extra_info['thumbnail']
    
    # Fallback username
    if not username:
        username = 'instagram_user'
    
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
    elif is_photo_post and len(items) == 1 and not is_carousel:
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
    ytdlp_input = sys.argv[4] if len(sys.argv) >= 5 else ''

    log_debug(f"URL: {url}")
    log_debug(f"Download path: {download_path}")
    log_debug(f"Has requests library: {HAS_REQUESTS}")
    log_debug(f"yt-dlp input: {ytdlp_input}")

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

    # Find working yt-dlp command
    ytdlp_cmd = find_ytdlp_command(ytdlp_input)
    log_debug(f"Using yt-dlp command: {' '.join(ytdlp_cmd)}")

    # Verify yt-dlp works
    return_code, stdout, stderr = run_ytdlp(ytdlp_cmd, ['--version'], timeout=15)
    if return_code != 0:
        log_debug(f"yt-dlp verification failed: {stderr[:200]}")
        # Don't exit here, we might be able to download photos without yt-dlp
    else:
        log_debug(f"yt-dlp version: {stdout.strip()}")

    # Try each cookie file
    last_error = None
    last_error_type = None
    cookies_tried = 0
    all_errors = []

    for idx, cookie_path in enumerate(cookie_files):
        cookies_tried += 1
        
        result, error_msg, error_type, should_retry = try_download(
            url, download_path, cookie_path, ytdlp_cmd, idx
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
        "ytdlp_cmd": ' '.join(ytdlp_cmd),
        "has_requests": HAS_REQUESTS
    }

    if last_error_type == "private_content":
        log_error("This content is from a private account.", "private_content", cookies_tried, debug_info)
    elif last_error_type == "not_found":
        log_error("This post was not found or has been removed.", "not_found", cookies_tried, debug_info)
    elif last_error_type == "ytdlp_error":
        log_error(
            "yt-dlp execution error. Please check yt-dlp installation.",
            "ytdlp_error",
            cookies_tried,
            debug_info
        )
    else:
        log_error(
            f"All {cookies_tried} cookie(s) failed. Please check cookie files and try again.",
            "all_cookies_failed",
            cookies_tried,
            debug_info
        )


if __name__ == "__main__":
    main()