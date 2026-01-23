#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
Phase 1: Cookie-based downloading using yt-dlp

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_path> [yt_dlp_path]

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


def fetch_metadata(url, cookies_path, ytdlp_bin='yt-dlp'):
    """Fetch metadata using yt-dlp --dump-json."""
    cmd = [
        ytdlp_bin,
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
            stderr_raw = (result.stderr or '')
            stdout_raw = (result.stdout or '')
            combined_raw = (stderr_raw + "\n" + stdout_raw).strip()
            combined = combined_raw.lower()

            if 'login' in combined or 'authentication' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Login required. Cookies may be expired. Details: {snippet}", "login_required"
            if 'private' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"This content is from a private account. Details: {snippet}", "private_content"
            if 'not found' in combined or '404' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"This post has been removed or doesn't exist. Details: {snippet}", "not_found"
            if 'rate' in combined or 'too many' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Rate limited by Instagram. Please try again later. Details: {snippet}", "rate_limited"

            # Not a cookie error: Instagram extractor sometimes fails to see video formats.
            # This is usually fixed by updating yt-dlp.
            if 'no video formats found' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"No downloadable video formats found for this URL. Try updating yt-dlp. Details: {snippet}", "no_formats"

            # If yt-dlp crashed (traceback), surface as a worker error (not cookies)
            if 'traceback' in combined or 'exception' in combined:
                # Return more context for debugging (first ~4000 chars)
                details = combined_raw.strip()
                details = details[:4000] if len(details) > 4000 else details
                return None, f"yt-dlp crashed while fetching metadata. Details: {details}", "ytdlp_crashed"

            # Cookie-related errors vary; match broader keywords
            cookie_keywords = ['cookie', 'cookies', 'csrf', 'sessionid', 'checkpoint', 'consent', 'authorization']
            if any(k in combined for k in cookie_keywords):
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Cookie/auth error. Please check cookie configuration. Details: {snippet}", "cookies_error"

            snippet = combined_raw.replace('\n', ' ')[:260]
            return None, f"Failed to fetch content. Details: {snippet}", "fetch_error"

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
        return None, f"yt-dlp binary not found: {ytdlp_bin}", "ytdlp_missing"
    except Exception as e:
        return None, f"Unexpected error: {str(e)}", "exception"

def download_media(url, download_path, cookies_path, ytdlp_bin='yt-dlp', want_thumbnails=True):
    """Download media using yt-dlp."""
    # Ensure download path exists
    Path(download_path).mkdir(parents=True, exist_ok=True)

    # Build output template
    output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')

    cmd = [
        ytdlp_bin,
        '--cookies', cookies_path,
        '--no-warnings',
        '--no-playlist-reverse',
        '-o', output_template,
        '--merge-output-format', 'mp4',
    ]

    # Only write thumbnails for video/reel content.
    # For image posts/carousels, thumbnails are the media itself and writing thumbnails causes confusion/broken previews.
    if want_thumbnails:
        cmd += ['--write-thumbnail', '--convert-thumbnails', 'jpg']

    cmd += [url]

    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=300  # 5 minute timeout for downloads
        )

        if result.returncode != 0:
            stderr_raw = (result.stderr or '')
            stdout_raw = (result.stdout or '')
            combined_raw = (stderr_raw + "\n" + stdout_raw).strip()
            combined = combined_raw.lower()

            if 'login' in combined or 'authentication' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Login required. Cookies may be expired. Details: {snippet}", "login_required"
            if 'private' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"This content is from a private account. Details: {snippet}", "private_content"
            if 'not found' in combined or '404' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"This post has been removed or doesn't exist. Details: {snippet}", "not_found"
            if 'rate' in combined or 'too many' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Rate limited by Instagram. Please try again later. Details: {snippet}", "rate_limited"

            if 'no video formats found' in combined:
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"No downloadable video formats found for this URL. Try updating yt-dlp. Details: {snippet}", "no_formats"

            if 'traceback' in combined or 'exception' in combined:
                details = combined_raw.strip()
                details = details[:4000] if len(details) > 4000 else details
                return None, f"yt-dlp crashed during download. Details: {details}", "ytdlp_crashed"

            cookie_keywords = ['cookie', 'cookies', 'csrf', 'sessionid', 'checkpoint', 'consent', 'authorization']
            if any(k in combined for k in cookie_keywords):
                snippet = combined_raw.replace('\n', ' ')[:260]
                return None, f"Cookie/auth error during download. Details: {snippet}", "cookies_error"

            # Check if files were downloaded despite error
            downloaded_files = list(Path(download_path).glob('*'))
            media_files = [f for f in downloaded_files if f.suffix.lower() in ['.mp4', '.webm', '.jpg', '.jpeg', '.png', '.webp']]

            if not media_files:
                snippet = (result.stderr or result.stdout or '').strip().replace('\n', ' ')[:220]
                return None, f"Download failed. Details: {snippet}", "download_error"

        # Find downloaded files
        downloaded_files = list(Path(download_path).glob('*'))

        # Separate actual media from thumbnails. yt-dlp can save thumbnails as .jpg.
        # We only want downloadable media items here.
        media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
        image_exts = {'.jpg', '.jpeg', '.png', '.webp'}

        all_files = [f for f in downloaded_files if f.is_file()]
        video_files = [f for f in all_files if f.suffix.lower() in {'.mp4', '.webm', '.mkv'}]

        # If there is at least one video, treat images as thumbnails/sidecars and exclude them from media items
        if video_files:
            media_files = sorted(video_files)
        else:
            # Pure image posts/carousels: keep images as media items
            media_files = sorted([f for f in all_files if f.suffix.lower() in image_exts])

        if not media_files:
            return None, "No media files were downloaded.", "no_files"

        return media_files, None, None

    except subprocess.TimeoutExpired:
        return None, "Download timed out. The file may be too large.", "timeout"
    except Exception as e:
        return None, f"Download error: {str(e)}", "exception"

def main():
    # Parse arguments
    if len(sys.argv) not in (4, 5):
        log_error("Usage: python instagram_fetch.py <url> <download_path> <cookies_path> [yt_dlp_path]", "invalid_args")
    
    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_path = sys.argv[3]
    ytdlp_bin = sys.argv[4] if len(sys.argv) == 5 and sys.argv[4] else 'yt-dlp'
    
    # Validate URL
    if not validate_url(url):
        log_error("Invalid Instagram URL format.", "invalid_url")

    # Preflight: confirm yt-dlp binary is runnable (helps diagnose VPS-only crashes)
    try:
        ver = subprocess.run([ytdlp_bin, '--version'], capture_output=True, text=True, timeout=15)
        if ver.returncode != 0:
            out = ((ver.stderr or '') + "\n" + (ver.stdout or '')).strip()
            out = out[:800] if len(out) > 800 else out
            log_error(f"yt-dlp failed to run. Binary: {ytdlp_bin}. Details: {out}", "ytdlp_crashed")
    except FileNotFoundError:
        log_error(f"yt-dlp binary not found: {ytdlp_bin}", "ytdlp_missing")
    except Exception as e:
        log_error(f"yt-dlp preflight check failed. Binary: {ytdlp_bin}. Error: {str(e)}", "ytdlp_crashed")
    
    # Check cookies file exists
    if not os.path.isfile(cookies_path):
        log_error("Cookies file not found. Please configure Instagram cookies.", "cookies_missing")
    
    # Check cookies file is not empty
    if os.path.getsize(cookies_path) == 0:
        log_error("Cookies file is empty. Please add valid Instagram cookies.", "cookies_empty")
    
    # Fetch metadata first
    info_dict, error, error_type = fetch_metadata(url, cookies_path, ytdlp_bin=ytdlp_bin)
    
    if error:
        log_error(error, error_type)
    
    # Download media
    # Only generate thumbnails for reels/videos (not for image posts/carousels)
    url_lower = url.lower()
    want_thumbnails = ('/reel/' in url_lower) or ('/reels/' in url_lower) or ('/tv/' in url_lower)
    media_files, error, error_type = download_media(url, download_path, cookies_path, ytdlp_bin=ytdlp_bin, want_thumbnails=want_thumbnails)
    
    if error:
        log_error(error, error_type)
    
    # Determine content type
    content_type = get_content_type(url, info_dict)

    # If URL is a reel but we ended up downloading only images, it's usually a thumbnail-only extraction.
    # Keep type as 'reel' but items will reflect actual downloads.
    
    # Extract metadata
    username = info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user'))
    caption = info_dict.get('description', info_dict.get('title', ''))
    thumbnail = info_dict.get('thumbnail', '')
    
    # Build items array (ONLY downloadable media items)
    items = []
    for i, file_path in enumerate(media_files):
        ext = file_path.suffix.lower().lstrip('.')
        is_video = ext in ['mp4', 'webm', 'mkv']

        # Find a thumbnail image generated by yt-dlp for this URL/download session.
        # Do NOT treat thumbnails as media items.
        thumb_path = None
        for thumb in Path(download_path).glob('*.jpg'):
            # Heuristic: match by Instagram id prefix or shared stem prefix
            if file_path.stem.split('_')[0] in thumb.stem:
                thumb_path = str(thumb)
                break

        item = {
            "id": i + 1,
            "type": "video" if is_video else "image",
            "format": ext,
            "quality": get_quality_label(info_dict) if is_video else "Original",
            "path": str(file_path),
            "filename": file_path.name,
            # 'thumbnail' is a REMOTE URL (safe to show in browser)
            "thumbnail": thumbnail,
            # 'thumbnail_file' is a LOCAL path (Laravel will convert it to thumbnail_url)
            "thumbnail_file": thumb_path or ""
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