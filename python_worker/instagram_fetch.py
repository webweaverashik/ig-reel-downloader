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
    # Try different fields where username might be
    username = info.get('uploader', '')
    if not username:
        username = info.get('uploader_id', '')
    if not username:
        username = info.get('channel', '')
    if not username:
        username = info.get('channel_id', '')
    if not username:
        # Try to extract from webpage_url
        webpage_url = info.get('webpage_url', '')
        match = re.search(r'instagram\.com/([^/]+)/', webpage_url)
        if match:
            username = match.group(1)
    
    return username or 'instagram_user'


def extract_formats(info):
    """Extract available formats from yt-dlp info."""
    formats = []
    raw_formats = info.get('formats', [])
    
    if not raw_formats:
        # Single format (likely a photo)
        url = info.get('url', '')
        if url:
            formats.append({
                'quality': 'Original',
                'format': 'jpg' if is_image_url(url) else 'mp4',
                'url': url
            })
        return formats
    
    # Track seen qualities to avoid duplicates
    seen_qualities = set()
    
    for fmt in raw_formats:
        format_id = fmt.get('format_id', '')
        url = fmt.get('url', '')
        ext = fmt.get('ext', 'mp4')
        height = fmt.get('height', 0)
        width = fmt.get('width', 0)
        
        if not url:
            continue
        
        # Determine quality label
        if height:
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
    
    # Sort by quality (highest first)
    formats.sort(key=lambda x: x.get('height', 0), reverse=True)
    
    # Limit to top 3 qualities to avoid clutter
    return formats[:3] if len(formats) > 3 else formats


def is_image_url(url):
    """Check if URL points to an image."""
    image_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp']
    return any(ext in url.lower() for ext in image_extensions)


def fetch_instagram_info(url):
    """Fetch Instagram media information using yt-dlp."""
    try:
        # Run yt-dlp with JSON output
        result = subprocess.run(
            ['yt-dlp', '--dump-json', '--skip-download', '--no-warnings', url],
            capture_output=True,
            text=True,
            timeout=60
        )
        
        if result.returncode != 0:
            error_msg = result.stderr.strip()
            
            # Parse common errors
            if 'Private' in error_msg or 'private' in error_msg:
                return {'error': 'This content is from a private account'}
            elif 'not exist' in error_msg.lower() or '404' in error_msg:
                return {'error': 'This post does not exist or has been removed'}
            elif 'login' in error_msg.lower() or 'authentication' in error_msg.lower():
                return {'error': 'Login required to access this content'}
            elif 'rate' in error_msg.lower() or 'limit' in error_msg.lower():
                return {'error': 'Rate limited. Please try again later'}
            else:
                return {'error': f'Failed to fetch: {error_msg[:100]}'}
        
        # Parse JSON output
        output = result.stdout.strip()
        
        # Handle multiple JSON objects (carousel posts)
        json_objects = []
        for line in output.split('\n'):
            if line.strip():
                try:
                    json_objects.append(json.loads(line))
                except json.JSONDecodeError:
                    continue
        
        if not json_objects:
            return {'error': 'No media information found'}
        
        # Use first object for main info
        info = json_objects[0]
        
        # Determine media type
        media_type = get_media_type(url)
        if media_type == 'post':
            # Check if it's a photo or video
            ext = info.get('ext', 'mp4')
            if ext in ['jpg', 'jpeg', 'png', 'webp', 'gif']:
                media_type = 'photo'
            else:
                media_type = 'video'
        
        # Extract formats from all items (for carousels)
        all_formats = []
        for obj in json_objects:
            all_formats.extend(extract_formats(obj))
        
        # Remove duplicates
        seen = set()
        unique_formats = []
        for fmt in all_formats:
            key = (fmt['quality'], fmt['format'], fmt.get('height', 0))
            if key not in seen:
                seen.add(key)
                unique_formats.append(fmt)
        
        # Build response
        response = {
            'type': media_type,
            'username': extract_username(info),
            'caption': info.get('description', '') or info.get('title', ''),
            'thumbnail': info.get('thumbnail', ''),
            'formats': unique_formats if unique_formats else [{
                'quality': 'Original',
                'format': 'mp4',
                'url': info.get('url', '')
            }]
        }
        
        return response
        
    except subprocess.TimeoutExpired:
        return {'error': 'Request timed out. Please try again'}
    except FileNotFoundError:
        return {'error': 'yt-dlp is not installed. Please install it with: pip install yt-dlp'}
    except Exception as e:
        return {'error': f'Unexpected error: {str(e)}'}


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No URL provided'}))
        sys.exit(1)
    
    url = sys.argv[1]
    
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