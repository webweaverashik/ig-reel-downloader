#!/usr/bin/env python3
"""
ig reel downloader - Instagram Media Fetcher
Uses yt-dlp ONLY for extracting Instagram media information.
Compatible with Windows (Laragon) and Ubuntu 24.04 VPS.

Usage: python instagram_fetch.py <instagram_url>
"""

import sys
import json
import subprocess
import re
import os


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


def extract_formats(info):
    """Extract available formats from yt-dlp info."""
    formats = []
    
    if not info:
        return formats
    
    raw_formats = info.get('formats', [])
    
    if not raw_formats:
        url = info.get('url', '')
        if url:
            formats.append({
                'quality': 'Original',
                'format': 'jpg' if is_image_url(url) else 'mp4',
                'url': url
            })
        return formats
    
    seen_qualities = set()
    
    for fmt in raw_formats:
        if not fmt:
            continue
            
        url = fmt.get('url', '')
        if not url:
            continue
            
        ext = safe_str(fmt.get('ext'), 'mp4')
        height = safe_int(fmt.get('height'), 0)
        width = safe_int(fmt.get('width'), 0)
        
        # Determine quality label
        if height > 0:
            if height >= 1080:
                quality = 'HD 1080p'
            elif height >= 720:
                quality = 'HD 720p'
            elif height >= 480:
                quality = 'SD 480p'
            elif height >= 360:
                quality = 'SD 360p'
            else:
                quality = f'{height}p'
        else:
            quality = 'Original'
        
        # Skip duplicates
        quality_key = f"{quality}_{ext}"
        if quality_key in seen_qualities:
            continue
        seen_qualities.add(quality_key)
        
        formats.append({
            'quality': quality,
            'format': ext,
            'url': url,
            'width': width,
            'height': height
        })
    
    # Sort by quality (highest first) - using safe_int for comparison
    formats.sort(key=lambda x: safe_int(x.get('height'), 0), reverse=True)
    
    # Limit to top 3 qualities
    return formats[:3] if len(formats) > 3 else formats


def is_image_url(url):
    """Check if URL points to an image."""
    if not url:
        return False
    image_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp']
    return any(ext in url.lower() for ext in image_extensions)


def find_ytdlp():
    """Find yt-dlp executable path."""
    # Common locations
    possible_paths = [
        'yt-dlp',  # In PATH
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        os.path.expanduser('~/.local/bin/yt-dlp'),
    ]
    
    # On Windows, also check Scripts folder
    if sys.platform == 'win32':
        possible_paths.extend([
            os.path.join(os.path.dirname(sys.executable), 'Scripts', 'yt-dlp.exe'),
            os.path.join(os.path.dirname(sys.executable), 'yt-dlp.exe'),
        ])
    
    for path in possible_paths:
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
    
    return 'yt-dlp'  # Default, hope it's in PATH


def fetch_instagram_info(url):
    """Fetch Instagram media information using yt-dlp."""
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
            
            # Parse common errors
            error_lower = error_msg.lower()
            if 'private' in error_lower:
                return {'error': 'This content is from a private account'}
            elif 'not exist' in error_lower or '404' in error_lower or 'not available' in error_lower:
                return {'error': 'This post does not exist or has been removed'}
            elif 'login' in error_lower or 'authentication' in error_lower:
                return {'error': 'Instagram requires login for this content. This post may not be publicly accessible.'}
            elif 'rate' in error_lower or 'limit' in error_lower:
                return {'error': 'Rate limited by Instagram. Please try again in a few minutes'}
            elif 'empty' in error_lower:
                return {'error': 'Instagram returned empty response. The post may require login or is not accessible.'}
            else:
                # Return truncated error
                return {'error': f'yt-dlp error: {error_msg[:150]}'}
        
        output = safe_str(result.stdout, '').strip()
        
        if not output:
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
        
        # Determine media type
        media_type = get_media_type(url)
        if media_type == 'post':
            ext = safe_str(info.get('ext'), 'mp4')
            if ext in ['jpg', 'jpeg', 'png', 'webp', 'gif']:
                media_type = 'photo'
            else:
                media_type = 'video'
        
        # Extract formats from all items (for carousels)
        all_formats = []
        for obj in json_objects:
            if obj:
                all_formats.extend(extract_formats(obj))
        
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