#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
Phase 1: Cookie-based downloading using yt-dlp

Supports: Reels, Videos, Photos, Carousels

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_path> <ytdlp_path>

Outputs JSON to stdout
"""

import sys
import os
import json
import subprocess
import re
import urllib.request
import ssl
from pathlib import Path

# Global yt-dlp path
YTDLP_PATH = 'yt-dlp'


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
    
    if info_dict.get('_type') == 'playlist':
        return 'carousel'
    
    # Check media type
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


def download_image(url, filepath):
    """Download image from URL."""
    try:
        # Create SSL context that doesn't verify (for Instagram CDN)
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE
        
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, context=ctx, timeout=30) as response:
            with open(filepath, 'wb') as f:
                f.write(response.read())
        return True
    except Exception as e:
        return False


def fetch_with_ytdlp(url, download_path, cookies_path, download=False):
    """Fetch info or download using yt-dlp."""
    env = os.environ.copy()
    env['HOME'] = os.environ.get('HOME', '/tmp')
    
    if download:
        output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')
        cmd = [
            YTDLP_PATH,
            '--cookies', cookies_path,
            '--no-warnings',
            '--no-playlist-reverse',
            '-o', output_template,
            '--write-thumbnail',
            '--convert-thumbnails', 'jpg',
            url
        ]
    else:
        cmd = [
            YTDLP_PATH,
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
            timeout=120 if download else 60,
            env=env,
            cwd=download_path if download else None
        )
        return result
    except subprocess.TimeoutExpired:
        return None
    except Exception as e:
        return None


def extract_image_urls(info_dict):
    """Extract image URLs from yt-dlp info (for photos/carousels)."""
    images = []
    
    # Check for thumbnails array (carousel images)
    thumbnails = info_dict.get('thumbnails', [])
    for thumb in thumbnails:
        url = thumb.get('url', '')
        if url and ('1080' in url or 'display' in url.lower()):
            images.append(url)
    
    # Check for display_url
    display_url = info_dict.get('display_url', '')
    if display_url:
        images.append(display_url)
    
    # Check thumbnail
    thumbnail = info_dict.get('thumbnail', '')
    if thumbnail and thumbnail not in images:
        images.append(thumbnail)
    
    return images


def main():
    global YTDLP_PATH
    
    # Parse arguments
    if len(sys.argv) < 4:
        log_error("Usage: python instagram_fetch.py <url> <download_path> <cookies_path> [ytdlp_path]", "invalid_args")
    
    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_path = sys.argv[3]
    
    if len(sys.argv) >= 5:
        YTDLP_PATH = sys.argv[4]
    
    # Validate
    if not validate_url(url):
        log_error("Invalid Instagram URL format.", "invalid_url")
    
    if not os.path.isfile(cookies_path):
        log_error("Cookies file not found.", "cookies_missing")
    
    if os.path.getsize(cookies_path) == 0:
        log_error("Cookies file is empty.", "cookies_empty")
    
    # Create download directory
    Path(download_path).mkdir(parents=True, exist_ok=True)
    
    # First, get metadata
    result = fetch_with_ytdlp(url, download_path, cookies_path, download=False)
    
    if result is None:
        log_error("Request timed out.", "timeout")
    
    # Parse metadata
    info_dict = None
    entries = []
    is_photo_only = False
    
    if result.returncode == 0 and result.stdout.strip():
        # Successfully got video info
        for line in result.stdout.strip().split('\n'):
            if line.strip():
                try:
                    entry = json.loads(line)
                    entries.append(entry)
                except:
                    pass
        
        if entries:
            info_dict = entries[0].copy()
            if len(entries) > 1:
                info_dict['entries'] = entries
                info_dict['_type'] = 'playlist'
    else:
        # Check if it's a "no video formats" error (likely a photo)
        stderr = result.stderr.lower() if result.stderr else ''
        
        if 'no video formats found' in stderr:
            is_photo_only = True
            # Try to extract info anyway for metadata
            # We'll need to download the image differently
        elif 'login' in stderr or 'authentication' in stderr:
            log_error("Login required. Please update cookies.", "login_required")
        elif 'private' in stderr:
            log_error("This is a private account.", "private_content")
        elif 'not found' in stderr or '404' in stderr:
            log_error("Post not found or removed.", "not_found")
        elif 'rate' in stderr:
            log_error("Rate limited. Try again later.", "rate_limited")
        else:
            # Unknown error - might still be a photo
            is_photo_only = True
    
    # Handle photo-only posts
    if is_photo_only or (info_dict is None):
        # For photo posts, we need to get the image URL differently
        # Try using yt-dlp with --list-thumbnails to get image URLs
        cmd = [
            YTDLP_PATH,
            '--cookies', cookies_path,
            '--list-thumbnails',
            '--no-warnings',
            url
        ]
        
        env = os.environ.copy()
        env['HOME'] = os.environ.get('HOME', '/tmp')
        
        try:
            thumb_result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=30,
                env=env
            )
            
            # Parse thumbnail URLs from output
            image_urls = []
            for line in thumb_result.stdout.split('\n'):
                # Look for high-res image URLs
                if 'http' in line and ('1080' in line or '1440' in line or 'display' in line.lower()):
                    parts = line.split()
                    for part in parts:
                        if part.startswith('http'):
                            image_urls.append(part)
                            break
            
            if not image_urls:
                # Fallback: try any URL with instagram
                for line in thumb_result.stdout.split('\n'):
                    if 'http' in line and 'instagram' in line:
                        parts = line.split()
                        for part in parts:
                            if part.startswith('http'):
                                if part not in image_urls:
                                    image_urls.append(part)
                                break
            
            # Download images
            items = []
            for i, img_url in enumerate(image_urls[:10]):  # Limit to 10 images
                ext = 'jpg'
                if '.webp' in img_url:
                    ext = 'webp'
                elif '.png' in img_url:
                    ext = 'png'
                
                filename = f"photo_{i+1}.{ext}"
                filepath = os.path.join(download_path, filename)
                
                if download_image(img_url, filepath):
                    items.append({
                        "id": i + 1,
                        "type": "image",
                        "format": ext,
                        "quality": "Original",
                        "path": filepath,
                        "filename": filename,
                        "thumbnail": filepath  # Use same image as thumbnail
                    })
            
            if items:
                # Extract post ID from URL for username placeholder
                post_id = url.rstrip('/').split('/')[-1]
                
                response = {
                    "success": True,
                    "type": "photo" if len(items) == 1 else "carousel",
                    "username": "instagram_user",
                    "caption": "",
                    "thumbnail": items[0]['path'] if items else "",
                    "items": items
                }
                print(json.dumps(response))
                sys.exit(0)
            else:
                log_error("Could not download images from this post.", "download_failed")
                
        except Exception as e:
            log_error(f"Error processing photo post: {str(e)}", "photo_error")
    
    # For video posts, download normally
    result = fetch_with_ytdlp(url, download_path, cookies_path, download=True)
    
    if result is None:
        log_error("Download timed out.", "timeout")
    
    if result.returncode != 0:
        stderr = result.stderr if result.stderr else ''
        # Check if files were downloaded despite error
        downloaded = list(Path(download_path).glob('*'))
        if not any(f.suffix.lower() in ['.mp4', '.webm', '.jpg', '.jpeg', '.png'] for f in downloaded):
            log_error(f"Download failed: {stderr[:200]}", "download_failed")
    
    # Find downloaded files
    downloaded_files = list(Path(download_path).glob('*'))
    media_files = [
        f for f in downloaded_files 
        if f.suffix.lower() in ['.mp4', '.webm', '.jpg', '.jpeg', '.png', '.webp']
        and '.thumb' not in f.stem.lower()
        and '_thumb' not in f.stem.lower()
    ]
    
    if not media_files:
        log_error("No media files were downloaded.", "no_files")
    
    # Build items
    items = []
    for i, file_path in enumerate(sorted(media_files)):
        ext = file_path.suffix.lower().lstrip('.')
        is_video = ext in ['mp4', 'webm', 'mkv']
        
        # Find thumbnail
        thumb_path = None
        thumb_patterns = [
            f"{file_path.stem}*.jpg",
            f"{file_path.stem.split('_')[0]}*.jpg",
        ]
        for pattern in thumb_patterns:
            matches = list(Path(download_path).glob(pattern))
            for m in matches:
                if m != file_path and 'thumb' in m.stem.lower():
                    thumb_path = str(m)
                    break
            if thumb_path:
                break
        
        item = {
            "id": i + 1,
            "type": "video" if is_video else "image",
            "format": ext,
            "quality": get_quality_label(info_dict) if info_dict and is_video else "Original",
            "path": str(file_path),
            "filename": file_path.name,
            "thumbnail": thumb_path or (info_dict.get('thumbnail', '') if info_dict else '')
        }
        items.append(item)
    
    # Determine content type
    content_type = 'video'
    if len(items) > 1:
        content_type = 'carousel'
    elif info_dict:
        content_type = get_content_type(url, info_dict)
    
    # Build response
    response = {
        "success": True,
        "type": content_type,
        "username": info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user')) if info_dict else 'instagram_user',
        "caption": (info_dict.get('description', '') or info_dict.get('title', ''))[:500] if info_dict else '',
        "thumbnail": info_dict.get('thumbnail', '') if info_dict else '',
        "items": items
    }
    
    print(json.dumps(response))


if __name__ == "__main__":
    main()