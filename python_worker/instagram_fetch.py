#!/usr/bin/env python3
"""
ig reel downloader - Instagram Media Fetcher
Uses yt-dlp for videos/reels and direct scraping for photos.
Compatible with Windows (Laragon) and Ubuntu 24.04 VPS.

Usage: python instagram_fetch.py <instagram_url>
"""

import sys
import json
import subprocess
import re
import os
import urllib.request
import urllib.error


def debug_log(message):
    """Print debug message to stderr (won't interfere with JSON output)."""
    print(f"[DEBUG] {message}", file=sys.stderr)


def get_media_type(url):
    """Determine the type of Instagram media from URL."""
    if '/reel/' in url or '/reels/' in url:
        return 'reel'
    elif '/tv/' in url:
        return 'video'
    elif '/p/' in url:
        return 'post'
    else:
        return 'unknown'


def extract_username(info):
    """Extract username from yt-dlp info."""
    if not info:
        return 'instagram_user'
    
    username = info.get('uploader', '')
    if not username:
        username = info.get('uploader_id', '')
    if not username:
        username = info.get('channel', '')
    if not username:
        username = info.get('channel_id', '')
    if not username:
        webpage_url = info.get('webpage_url', '')
        match = re.search(r'instagram\.com/([^/]+)/', webpage_url)
        if match:
            username = match.group(1)
    
    return username or 'instagram_user'


def safe_int(value, default=0):
    """Safely convert value to int, return default if None or invalid."""
    if value is None:
        return default
    try:
        return int(value)
    except (ValueError, TypeError):
        return default


def safe_str(value, default=''):
    """Safely convert value to string."""
    if value is None:
        return default
    return str(value)


def extract_formats_video(info):
    """Extract available video formats from yt-dlp info - with audio merged."""
    formats = []
    
    if not info:
        return formats
    
    raw_formats = info.get('formats', [])
    
    if not raw_formats:
        url = info.get('url', '')
        if url:
            formats.append({
                'quality': 'Original',
                'format': 'mp4',
                'url': url
            })
        return formats
    
    # Find best audio format
    audio_url = None
    for fmt in raw_formats:
        if fmt and fmt.get('acodec') and fmt.get('acodec') != 'none' and fmt.get('vcodec') == 'none':
            audio_url = fmt.get('url')
            break
    
    # Find video formats with audio already included OR the best format
    video_with_audio = []
    video_only = []
    
    for fmt in raw_formats:
        if not fmt:
            continue
        
        url = fmt.get('url', '')
        if not url:
            continue
        
        vcodec = fmt.get('vcodec', '')
        acodec = fmt.get('acodec', '')
        height = safe_int(fmt.get('height'), 0)
        
        # Skip audio-only formats
        if vcodec == 'none' or not vcodec:
            continue
        
        # Check if this format has audio
        has_audio = acodec and acodec != 'none'
        
        format_info = {
            'url': url,
            'height': height,
            'has_audio': has_audio,
            'format_id': fmt.get('format_id', ''),
            'ext': fmt.get('ext', 'mp4')
        }
        
        if has_audio:
            video_with_audio.append(format_info)
        else:
            video_only.append(format_info)
    
    # Prefer formats with audio, otherwise use video-only (highest quality first)
    all_videos = video_with_audio + video_only
    all_videos.sort(key=lambda x: x['height'], reverse=True)
    
    seen_qualities = set()
    
    for vid in all_videos:
        height = vid['height']
        
        # Determine quality label
        if height >= 1080:
            quality = 'HD 1080p'
        elif height >= 720:
            quality = 'HD 720p'
        elif height >= 480:
            quality = 'SD 480p'
        elif height >= 360:
            quality = 'SD 360p'
        elif height > 0:
            quality = f'{height}p'
        else:
            quality = 'Original'
        
        # Skip duplicates
        if quality in seen_qualities:
            continue
        seen_qualities.add(quality)
        
        # Add note about audio
        quality_label = quality
        if not vid['has_audio']:
            quality_label = f"{quality} (video)"
        
        formats.append({
            'quality': quality_label,
            'format': vid['ext'],
            'url': vid['url'],
            'height': height,
            'has_audio': vid['has_audio']
        })
    
    # Also add the best merged format (yt-dlp's default selection)
    best_url = info.get('url', '')
    if best_url and not any(f['url'] == best_url for f in formats):
        formats.insert(0, {
            'quality': 'Best Quality',
            'format': 'mp4',
            'url': best_url,
            'height': 0,
            'has_audio': True
        })
    
    # Limit to top 4 qualities
    return formats[:4] if len(formats) > 4 else formats


def is_image_url(url):
    """Check if URL points to an image."""
    if not url:
        return False
    image_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp']
    return any(ext in url.lower() for ext in image_extensions)


def find_ytdlp():
    """Find yt-dlp executable path."""
    import shutil
    
    # First try shutil.which (most reliable)
    ytdlp_path = shutil.which('yt-dlp')
    if ytdlp_path:
        debug_log(f"Found yt-dlp via shutil.which: {ytdlp_path}")
        return ytdlp_path
    
    # Common locations to check
    possible_paths = []
    
    # On Windows, check common Python Scripts locations
    if sys.platform == 'win32':
        user_base = os.path.expanduser('~')
        possible_paths.extend([
            # Standalone installation (your setup)
            'C:\\yt-dlp\\yt-dlp.exe',
            # Python Scripts locations
            os.path.join(os.path.dirname(sys.executable), 'Scripts', 'yt-dlp.exe'),
            os.path.join(os.path.dirname(sys.executable), 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Local', 'Programs', 'Python', 'Python313', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Local', 'Programs', 'Python', 'Python312', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Local', 'Programs', 'Python', 'Python311', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Local', 'Programs', 'Python', 'Python310', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Local', 'Microsoft', 'WindowsApps', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Roaming', 'Python', 'Python313', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Roaming', 'Python', 'Python312', 'Scripts', 'yt-dlp.exe'),
            os.path.join(user_base, 'AppData', 'Roaming', 'Python', 'Python311', 'Scripts', 'yt-dlp.exe'),
            # Laragon paths
            'C:\\laragon\\bin\\python\\python-3.10\\Scripts\\yt-dlp.exe',
            'C:\\laragon\\bin\\python\\Scripts\\yt-dlp.exe',
        ])
    else:
        # Linux/macOS
        possible_paths.extend([
            '/usr/local/bin/yt-dlp',
            '/usr/bin/yt-dlp',
            os.path.expanduser('~/.local/bin/yt-dlp'),
            '/home/{}/.local/bin/yt-dlp'.format(os.environ.get('USER', 'root')),
        ])
    
    # Test each path
    for path in possible_paths:
        if os.path.isfile(path):
            debug_log(f"Found yt-dlp at: {path}")
            try:
                result = subprocess.run(
                    [path, '--version'],
                    capture_output=True,
                    text=True,
                    timeout=5
                )
                if result.returncode == 0:
                    return path
            except:
                continue
    
    # Last resort: try running yt-dlp directly (might work if in PATH)
    debug_log("Trying 'yt-dlp' directly...")
    return 'yt-dlp'


def fetch_photo_info(url, shortcode):
    """Fetch Instagram photo/carousel info using web scraping."""
    debug_log(f"Attempting to fetch photo info for: {shortcode}")
    
    try:
        # Try multiple methods to get photo
        images = []
        username = 'instagram_user'
        caption = ''
        
        # Method 1: Try Instagram's embed endpoint
        embed_url = f"https://www.instagram.com/p/{shortcode}/embed/captioned/"
        debug_log(f"Trying embed URL: {embed_url}")
        
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Referer': 'https://www.instagram.com/',
        }
        
        request = urllib.request.Request(embed_url, headers=headers)
        
        try:
            with urllib.request.urlopen(request, timeout=30) as response:
                html = response.read().decode('utf-8', errors='ignore')
            
            debug_log(f"Got embed HTML, length: {len(html)}")
            
            # Extract image URLs from embed page - multiple patterns
            image_patterns = [
                r'"display_url"\s*:\s*"([^"]+)"',
                r'"src"\s*:\s*"(https://[^"]*scontent[^"]*\.jpg[^"]*)"',
                r'"src"\s*:\s*"(https://[^"]*cdninstagram[^"]*\.jpg[^"]*)"',
                r'class="EmbeddedMediaImage"[^>]*src="([^"]+)"',
                r'<img[^>]*class="[^"]*"[^>]*src="(https://[^"]*instagram[^"]*)"',
                r'srcSet="([^"\s]+)',
                r'src="(https://scontent[^"]+)"',
            ]
            
            for pattern in image_patterns:
                matches = re.findall(pattern, html)
                debug_log(f"Pattern {pattern[:30]}... found {len(matches)} matches")
                for match in matches:
                    # Unescape URL
                    img_url = match.replace('\\u0026', '&').replace('\\/', '/').replace('&amp;', '&')
                    # Filter valid Instagram image URLs
                    if img_url not in images and ('scontent' in img_url or 'cdninstagram' in img_url):
                        if '.jpg' in img_url or '.png' in img_url or '.webp' in img_url:
                            images.append(img_url)
                            debug_log(f"Found image: {img_url[:80]}...")
            
            # Extract username
            username_patterns = [
                r'"username"\s*:\s*"([^"]+)"',
                r'href="/([a-zA-Z0-9_.]+)/\?',
                r'@([a-zA-Z0-9_.]+)',
            ]
            for pattern in username_patterns:
                match = re.search(pattern, html)
                if match:
                    username = match.group(1)
                    debug_log(f"Found username: {username}")
                    break
            
            # Extract caption
            caption_patterns = [
                r'"caption"\s*:\s*"([^"]*)"',
                r'"text"\s*:\s*"([^"]*)"',
                r'<div class="Caption"[^>]*>.*?<a[^>]*>[^<]*</a>([^<]+)',
            ]
            for pattern in caption_patterns:
                match = re.search(pattern, html, re.DOTALL)
                if match:
                    caption = match.group(1)
                    caption = caption.replace('\\n', '\n').replace('\\u0026', '&').replace('&amp;', '&')
                    break
                    
        except urllib.error.HTTPError as e:
            debug_log(f"Embed request failed with HTTP {e.code}")
        except Exception as e:
            debug_log(f"Embed request failed: {str(e)}")
        
        # Method 2: Try media endpoint if embed didn't work
        if not images:
            media_url = f"https://www.instagram.com/p/{shortcode}/media/?size=l"
            debug_log(f"Trying media URL: {media_url}")
            
            try:
                request = urllib.request.Request(media_url, headers=headers)
                request.method = 'HEAD'  # Just check headers
                
                with urllib.request.urlopen(request, timeout=15) as response:
                    final_url = response.url
                    content_type = response.headers.get('Content-Type', '')
                    debug_log(f"Media URL redirects to: {final_url}")
                    debug_log(f"Content-Type: {content_type}")
                    
                    if 'image' in content_type:
                        images.append(final_url)
                        
            except Exception as e:
                debug_log(f"Media URL failed: {str(e)}")
        
        # Method 3: Try oembed endpoint
        if not images:
            oembed_url = f"https://api.instagram.com/oembed/?url=https://www.instagram.com/p/{shortcode}/"
            debug_log(f"Trying oembed URL: {oembed_url}")
            
            try:
                request = urllib.request.Request(oembed_url, headers=headers)
                
                with urllib.request.urlopen(request, timeout=15) as response:
                    data = json.loads(response.read().decode('utf-8'))
                    debug_log(f"Oembed response: {json.dumps(data)[:200]}")
                    
                    if data.get('thumbnail_url'):
                        images.append(data['thumbnail_url'])
                    if data.get('author_name'):
                        username = data['author_name']
                    if data.get('title'):
                        caption = data['title']
                        
            except Exception as e:
                debug_log(f"Oembed failed: {str(e)}")
        
        if images:
            debug_log(f"Total images found: {len(images)}")
            
            # Remove duplicates while preserving order
            seen = set()
            unique_images = []
            for img in images:
                if img not in seen:
                    seen.add(img)
                    unique_images.append(img)
            images = unique_images
            
            # Build formats list
            formats = []
            for i, img_url in enumerate(images[:5]):  # Limit to 5 images
                label = 'Original' if len(images) == 1 else f'Image {i+1}'
                formats.append({
                    'quality': label,
                    'format': 'jpg',
                    'url': img_url
                })
            
            return {
                'type': 'photo',
                'username': username,
                'caption': caption[:500] if caption else '',
                'thumbnail': images[0] if images else '',
                'formats': formats
            }
        
        debug_log("No images found with any method")
        return None
        
    except Exception as e:
        debug_log(f"Photo fetch error: {str(e)}")
        import traceback
        debug_log(traceback.format_exc())
        return None


def fetch_instagram_info(url):
    """Fetch Instagram media information using yt-dlp or scraping for photos."""
    
    # Extract shortcode from URL
    shortcode_match = re.search(r'/(?:p|reel|reels|tv)/([A-Za-z0-9_-]+)', url)
    shortcode = shortcode_match.group(1) if shortcode_match else None
    
    media_type = get_media_type(url)
    
    # First, try yt-dlp for videos/reels
    try:
        ytdlp_path = find_ytdlp()
        debug_log(f"Using yt-dlp: {ytdlp_path}")
        
        # Build command with options to bypass some restrictions
        cmd = [
            ytdlp_path,
            '--dump-json',
            '--skip-download',
            '--no-warnings',
            '--no-check-certificates',
            '--user-agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            '--referer', 'https://www.instagram.com/',
            '--add-header', 'Accept-Language:en-US,en;q=0.9',
            url
        ]
        
        debug_log(f"Running command: {' '.join(cmd)}")
        
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=60
        )
        
        debug_log(f"Return code: {result.returncode}")
        debug_log(f"Stdout length: {len(result.stdout) if result.stdout else 0}")
        debug_log(f"Stderr: {result.stderr[:200] if result.stderr else 'None'}")
        
        if result.returncode != 0:
            error_msg = safe_str(result.stderr, '').strip()
            error_lower = error_msg.lower()
            
            # If it's a "no video" error, try photo scraping
            if 'no video' in error_lower and shortcode:
                debug_log("No video found, trying photo scraping...")
                photo_result = fetch_photo_info(url, shortcode)
                if photo_result:
                    debug_log(f"Photo scraping succeeded: {len(photo_result.get('formats', []))} formats")
                    return photo_result
                else:
                    debug_log("Photo scraping failed, no images found")
                    return {'error': 'Could not extract photo. The post may be private or restricted.'}
            
            # Parse common errors
            if 'private' in error_lower:
                return {'error': 'This content is from a private account'}
            elif 'not exist' in error_lower or '404' in error_lower or 'not available' in error_lower:
                return {'error': 'This post does not exist or has been removed'}
            elif 'login' in error_lower or 'authentication' in error_lower:
                # Try photo scraping as fallback
                if shortcode:
                    photo_result = fetch_photo_info(url, shortcode)
                    if photo_result:
                        return photo_result
                return {'error': 'Instagram requires login for this content. This post may not be publicly accessible.'}
            elif 'rate' in error_lower or 'limit' in error_lower:
                return {'error': 'Rate limited by Instagram. Please try again in a few minutes'}
            elif 'empty' in error_lower:
                # Try photo scraping as fallback
                if shortcode:
                    photo_result = fetch_photo_info(url, shortcode)
                    if photo_result:
                        return photo_result
                return {'error': 'Instagram returned empty response. The post may require login or is not accessible.'}
            else:
                # Return truncated error
                return {'error': f'yt-dlp error: {error_msg[:150]}'}
        
        output = safe_str(result.stdout, '').strip()
        
        if not output:
            # Try photo scraping as fallback
            if shortcode:
                photo_result = fetch_photo_info(url, shortcode)
                if photo_result:
                    return photo_result
            return {'error': 'No data received from Instagram. The post may require login.'}
        
        # Handle multiple JSON objects (carousel posts)
        json_objects = []
        for line in output.split('\n'):
            line = line.strip()
            if line:
                try:
                    json_objects.append(json.loads(line))
                except json.JSONDecodeError:
                    continue
        
        if not json_objects:
            return {'error': 'Could not parse Instagram response'}
        
        # Use first object for main info
        info = json_objects[0]
        
        if not info:
            return {'error': 'Empty response from Instagram'}
        
        # Determine media type from actual content
        ext = safe_str(info.get('ext'), 'mp4')
        if ext in ['jpg', 'jpeg', 'png', 'webp', 'gif']:
            media_type = 'photo'
        elif media_type == 'post':
            media_type = 'video'
        
        # Extract formats from all items (for carousels)
        all_formats = []
        for obj in json_objects:
            if obj:
                all_formats.extend(extract_formats_video(obj))
        
        # Remove duplicates
        seen = set()
        unique_formats = []
        for fmt in all_formats:
            if fmt:
                key = (fmt.get('quality', ''), fmt.get('format', ''), safe_int(fmt.get('height'), 0))
                if key not in seen:
                    seen.add(key)
                    unique_formats.append(fmt)
        
        # Build response
        response = {
            'type': media_type,
            'username': extract_username(info),
            'caption': safe_str(info.get('description')) or safe_str(info.get('title')) or '',
            'thumbnail': safe_str(info.get('thumbnail')) or '',
            'formats': unique_formats if unique_formats else [{
                'quality': 'Original',
                'format': 'mp4',
                'url': safe_str(info.get('url'))
            }]
        }
        
        return response
        
    except subprocess.TimeoutExpired:
        return {'error': 'Request timed out. Please try again'}
    except FileNotFoundError as e:
        return {'error': f'yt-dlp not found. Please install: pip install yt-dlp. Details: {str(e)}'}
    except json.JSONDecodeError as e:
        return {'error': f'Failed to parse response: {str(e)}'}
    except Exception as e:
        return {'error': f'Unexpected error: {type(e).__name__}: {str(e)}'}


def clean_url(url):
    """Clean Instagram URL by removing tracking parameters."""
    # Remove query parameters that aren't needed
    if '?' in url:
        base_url = url.split('?')[0]
        # Ensure URL ends properly
        if not base_url.endswith('/'):
            base_url += '/'
        return base_url
    if not url.endswith('/'):
        url += '/'
    return url


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No URL provided'}))
        sys.exit(1)
    
    url = sys.argv[1].strip()
    debug_log(f"Input URL: {url}")
    
    # Clean the URL
    url = clean_url(url)
    debug_log(f"Cleaned URL: {url}")
    
    # Validate URL format
    instagram_patterns = [
        r'^https?://(www\.)?instagram\.com/p/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/reel/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/reels/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/tv/[\w-]+/?',
        r'^https?://(www\.)?instagram\.com/[\w.]+/reel/[\w-]+/?'
    ]
    
    is_valid = any(re.match(pattern, url) for pattern in instagram_patterns)
    
    if not is_valid:
        print(json.dumps({'error': 'Invalid Instagram URL format'}))
        sys.exit(1)
    
    result = fetch_instagram_info(url)
    print(json.dumps(result))
    
    if 'error' in result:
        sys.exit(1)
    
    sys.exit(0)


if __name__ == '__main__':
    main()