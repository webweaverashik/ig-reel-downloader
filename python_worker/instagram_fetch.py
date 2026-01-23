#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
Phase 1: Cookie-based downloading using yt-dlp

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_path>

Outputs JSON to stdout with:
- type: reel | video | photo | carousel
- username: Instagram username
- caption: Post caption
- thumbnail: Thumbnail URL
- items: Array of downloaded files with paths and metadata

Error handling:
- Missing/expired cookies
- Login required
- Private content
- Removed posts
- Rate limiting
"""

import sys
import os
import json
import subprocess
import re
from pathlib import Path


def log_error(message, error_type="unknown"):
    """Output error as JSON and exit."""
    print(json.dumps({
        "error": message,
        "error_type": error_type
    }))
    sys.exit(1)


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


def get_content_type(url, info_dict):
    """Determine content type from URL and metadata."""
    url_lower = url.lower()
    
    if '/reel/' in url_lower or '/reels/' in url_lower:
        return 'reel'
    if '/stories/' in url_lower:
        return 'story'
    if '/tv/' in url_lower:
        return 'video'
    
    # Check if it's a carousel (multiple entries)
    entries = info_dict.get('entries', [])
    if len(entries) > 1:
        return 'carousel'
    
    # Check media type from yt-dlp info
    if info_dict.get('_type') == 'playlist':
        return 'carousel'
    
    # Check if it's a video or image
    ext = info_dict.get('ext', '')
    if ext in ['mp4', 'webm', 'mkv']:
        return 'video'
    if ext in ['jpg', 'jpeg', 'png', 'webp']:
        return 'photo'
    
    return 'post'


def get_quality_label(info_dict):
    """Get human-readable quality label."""
    height = info_dict.get('height', 0)
    width = info_dict.get('width', 0)
    
    if height >= 1080 or width >= 1920:
        return 'HD 1080p'
    if height >= 720 or width >= 1280:
        return 'HD 720p'
    if height >= 480 or width >= 854:
        return 'SD 480p'
    if height > 0:
        return f'{height}p'
    
    return 'Original'


def fetch_metadata(url, cookies_path):
    """Fetch metadata using yt-dlp --dump-json."""
    cmd = [
        'yt-dlp',
        '--cookies', cookies_path,
        '--dump-json',
        '--no-download',
        '--no-warnings',
        url
    ]
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=60
        )
        
        if result.returncode != 0:
            stderr = result.stderr.lower()
            
            if 'login' in stderr or 'authentication' in stderr:
                return None, "Login required. Cookies may be expired.", "login_required"
            if 'private' in stderr:
                return None, "This content is from a private account.", "private_content"
            if 'not found' in stderr or '404' in stderr:
                return None, "This post has been removed or doesn't exist.", "not_found"
            if 'rate' in stderr or 'too many' in stderr:
                return None, "Rate limited by Instagram. Please try again later.", "rate_limited"
            if 'cookies' in stderr:
                return None, "Cookie error. Please check cookie configuration.", "cookies_error"
            
            return None, f"Failed to fetch content: {result.stderr[:200]}", "fetch_error"
        
        # Parse JSON output (might be multiple lines for carousel)
        output_lines = result.stdout.strip().split('\n')
        entries = []
        
        for line in output_lines:
            if line.strip():
                try:
                    entry = json.loads(line)
                    entries.append(entry)
                except json.JSONDecodeError:
                    continue
        
        if not entries:
            return None, "No content found at this URL.", "no_content"
        
        # Return first entry as main info, with all entries for carousel
        main_info = entries[0].copy()
        if len(entries) > 1:
            main_info['entries'] = entries
            main_info['_type'] = 'playlist'
        
        return main_info, None, None
        
    except subprocess.TimeoutExpired:
        return None, "Request timed out. Please try again.", "timeout"
    except FileNotFoundError:
        return None, "yt-dlp is not installed or not in PATH.", "ytdlp_missing"
    except Exception as e:
        return None, f"Unexpected error: {str(e)}", "exception"


def download_media(url, download_path, cookies_path):
    """Download media using yt-dlp."""
    # Ensure download path exists
    Path(download_path).mkdir(parents=True, exist_ok=True)
    
    # Build output template
    output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')
    
    cmd = [
        'yt-dlp',
        '--cookies', cookies_path,
        '--no-warnings',
        '--no-playlist-reverse',
        '-o', output_template,
        '--merge-output-format', 'mp4',
        '--write-thumbnail',
        '--convert-thumbnails', 'jpg',
        url
    ]
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=300  # 5 minute timeout for downloads
        )
        
        if result.returncode != 0:
            stderr = result.stderr.lower()
            
            if 'login' in stderr or 'authentication' in stderr:
                return None, "Login required. Cookies may be expired.", "login_required"
            if 'private' in stderr:
                return None, "This content is from a private account.", "private_content"
            if 'not found' in stderr or '404' in stderr:
                return None, "This post has been removed or doesn't exist.", "not_found"
            if 'rate' in stderr or 'too many' in stderr:
                return None, "Rate limited by Instagram. Please try again later.", "rate_limited"
            
            # Check if files were downloaded despite error
            downloaded_files = list(Path(download_path).glob('*'))
            media_files = [f for f in downloaded_files if f.suffix.lower() in ['.mp4', '.webm', '.jpg', '.jpeg', '.png', '.webp']]
            
            if not media_files:
                return None, f"Download failed: {result.stderr[:200]}", "download_error"
        
        # Find downloaded files
        downloaded_files = list(Path(download_path).glob('*'))
        media_files = [f for f in downloaded_files if f.suffix.lower() in ['.mp4', '.webm', '.jpg', '.jpeg', '.png', '.webp'] and '.thumb' not in f.stem]
        thumbnail_files = [f for f in downloaded_files if '.thumb' in f.stem or f.stem.endswith('_thumbnail')]
        
        if not media_files:
            return None, "No media files were downloaded.", "no_files"
        
        return media_files, None, None
        
    except subprocess.TimeoutExpired:
        return None, "Download timed out. The file may be too large.", "timeout"
    except Exception as e:
        return None, f"Download error: {str(e)}", "exception"


def main():
    # Parse arguments
    if len(sys.argv) != 4:
        log_error("Usage: python instagram_fetch.py <url> <download_path> <cookies_path>", "invalid_args")
    
    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_path = sys.argv[3]
    
    # Validate URL
    if not validate_url(url):
        log_error("Invalid Instagram URL format.", "invalid_url")
    
    # Check cookies file exists
    if not os.path.isfile(cookies_path):
        log_error("Cookies file not found. Please configure Instagram cookies.", "cookies_missing")
    
    # Check cookies file is not empty
    if os.path.getsize(cookies_path) == 0:
        log_error("Cookies file is empty. Please add valid Instagram cookies.", "cookies_empty")
    
    # Fetch metadata first
    info_dict, error, error_type = fetch_metadata(url, cookies_path)
    
    if error:
        log_error(error, error_type)
    
    # Download media
    media_files, error, error_type = download_media(url, download_path, cookies_path)
    
    if error:
        log_error(error, error_type)
    
    # Determine content type
    content_type = get_content_type(url, info_dict)
    
    # Extract metadata
    username = info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user'))
    caption = info_dict.get('description', info_dict.get('title', ''))
    thumbnail = info_dict.get('thumbnail', '')
    
    # Build items array
    items = []
    for i, file_path in enumerate(sorted(media_files)):
        ext = file_path.suffix.lower().lstrip('.')
        is_video = ext in ['mp4', 'webm', 'mkv']
        
        # Try to find corresponding thumbnail
        thumb_path = None
        for thumb in Path(download_path).glob('*.jpg'):
            if file_path.stem in thumb.stem or thumb.stem.startswith(file_path.stem.split('_')[0]):
                thumb_path = str(thumb)
                break
        
        item = {
            "id": i + 1,
            "type": "video" if is_video else "image",
            "format": ext,
            "quality": get_quality_label(info_dict) if is_video else "Original",
            "path": str(file_path),
            "filename": file_path.name,
            "thumbnail": thumb_path or thumbnail
        }
        items.append(item)
    
    # If it's a carousel with multiple items, update type
    if len(items) > 1:
        content_type = 'carousel'
    
    # Build response
    response = {
        "success": True,
        "type": content_type,
        "username": username,
        "caption": caption[:500] if caption else "",  # Limit caption length
        "thumbnail": thumbnail,
        "items": items
    }
    
    # Output JSON
    print(json.dumps(response))


if __name__ == "__main__":
    main()