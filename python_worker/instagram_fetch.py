#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
IGReelDownloader.net

Cookie-based downloading using yt-dlp with multiple cookie support.
Supports: Reels, Videos, Photos, Stories, Carousel posts.

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_json> [yt_dlp_path]

Arguments:
    instagram_url  - The Instagram URL to download
    download_path  - Directory to save downloaded files
    cookies_json   - JSON array of cookie file paths to try sequentially
    yt_dlp_path    - Optional path to yt-dlp binary (default: yt-dlp)

Outputs JSON to stdout with:
- success: boolean
- type: reel | video | photo | carousel | story
- username: Instagram username
- caption: Post caption
- thumbnail: Thumbnail URL
- items: Array of downloaded files with paths and metadata
"""

import sys
import os
import json
import subprocess
import re
import shutil
from pathlib import Path


def log_error(message, error_type="unknown", cookies_tried=0):
    """Output error as JSON and exit."""
    output = {
        "success": False,
        "error": str(message)[:500],
        "error_type": error_type,
        "cookies_tried": cookies_tried
    }
    print(json.dumps(output))
    sys.exit(1)


def log_debug(message):
    """Log debug message to stderr (won't interfere with JSON output)."""
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


def is_cookie_error(error_text):
    """Check if error is related to cookies/authentication."""
    if not error_text:
        return False
    error_lower = error_text.lower()
    cookie_keywords = [
        'login', 'authentication', 'cookie', 'cookies',
        'csrf', 'sessionid', 'checkpoint', 'consent',
        'authorization', 'logged in', 'sign in',
        'rate limit', 'too many requests', '429',
        'please wait', 'try again later',
        'unauthorized', '401', '403'
    ]
    return any(keyword in error_lower for keyword in cookie_keywords)


def is_permanent_error(error_text):
    """Check if error is permanent and shouldn't retry with different cookie."""
    if not error_text:
        return False
    error_lower = error_text.lower()
    permanent_keywords = [
        'private', 'not found', '404', 'removed', 'deleted',
        'does not exist', 'unavailable', 'blocked', 'suspended'
    ]
    return any(keyword in error_lower for keyword in permanent_keywords)


def get_content_type(url, info_dict=None):
    """Determine content type from URL and metadata."""
    url_lower = url.lower()

    if '/reel/' in url_lower or '/reels/' in url_lower:
        return 'reel'
    if '/stories/' in url_lower:
        return 'story'
    if '/tv/' in url_lower:
        return 'video'

    if info_dict:
        # Check if it's a carousel (multiple entries)
        entries = info_dict.get('entries', [])
        if len(entries) > 1:
            return 'carousel'

        if info_dict.get('_type') == 'playlist':
            return 'carousel'

        # Check media type from yt-dlp info
        ext = info_dict.get('ext', '')
        if ext in ['mp4', 'webm', 'mkv']:
            return 'video'
        if ext in ['jpg', 'jpeg', 'png', 'webp']:
            return 'photo'

    # Default based on URL pattern
    if '/p/' in url_lower:
        return 'post'

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


def find_ytdlp_binary(ytdlp_path):
    """Find a working yt-dlp binary."""
    # Try provided path first
    if ytdlp_path and os.path.isfile(ytdlp_path):
        return ytdlp_path

    # Try common locations
    candidates = [
        ytdlp_path,
        'yt-dlp',
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        shutil.which('yt-dlp'),
    ]

    for candidate in candidates:
        if candidate:
            try:
                result = subprocess.run(
                    [candidate, '--version'],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                if result.returncode == 0:
                    log_debug(f"Found yt-dlp: {candidate} (version: {result.stdout.strip()})")
                    return candidate
            except (subprocess.TimeoutExpired, FileNotFoundError, PermissionError):
                continue

    return None


def run_ytdlp(args, timeout=120, env=None):
    """Run yt-dlp with proper error handling."""
    try:
        # Merge environment
        run_env = os.environ.copy()
        if env:
            run_env.update(env)

        # Ensure HOME is set (needed on some systems)
        if 'HOME' not in run_env:
            run_env['HOME'] = '/tmp'

        result = subprocess.run(
            args,
            capture_output=True,
            text=True,
            timeout=timeout,
            env=run_env
        )

        return result.returncode, result.stdout, result.stderr

    except subprocess.TimeoutExpired:
        return -2, '', 'Request timed out'
    except FileNotFoundError as e:
        return -3, '', f'Binary not found: {str(e)}'
    except PermissionError as e:
        return -4, '', f'Permission denied: {str(e)}'
    except Exception as e:
        return -5, '', f'Unexpected error: {str(e)}'


def download_media(url, download_path, cookies_path, ytdlp_bin, content_type='post'):
    """Download media using yt-dlp."""
    # Ensure download path exists
    Path(download_path).mkdir(parents=True, exist_ok=True)

    # Build output template
    output_template = os.path.join(download_path, '%(id)s_%(autonumber)s.%(ext)s')

    # Base command
    cmd = [
        ytdlp_bin,
        '--cookies', cookies_path,
        '--no-warnings',
        '--no-playlist-reverse',
        '--socket-timeout', '30',
        '--retries', '3',
        '-o', output_template,
    ]

    # For videos/reels, merge to mp4 and get thumbnails
    if content_type in ['reel', 'video', 'tv', 'story']:
        cmd += [
            '--merge-output-format', 'mp4',
            '--write-thumbnail',
            '--convert-thumbnails', 'jpg'
        ]

    cmd.append(url)

    log_debug(f"Running download command: {' '.join(cmd[:4])}...")

    return_code, stdout, stderr = run_ytdlp(cmd, timeout=300)

    combined_output = (stdout + '\n' + stderr).strip()

    if return_code != 0:
        # Check if any files were downloaded despite error
        downloaded_files = list(Path(download_path).glob('*'))
        media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
        media_files = [f for f in downloaded_files if f.suffix.lower() in media_exts and f.is_file()]

        if media_files:
            log_debug(f"Found {len(media_files)} media files despite return code {return_code}")
            return sorted(media_files), None

        return None, combined_output

    # Find downloaded files
    downloaded_files = list(Path(download_path).glob('*'))

    # Separate actual media from thumbnails
    media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
    image_exts = {'.jpg', '.jpeg', '.png', '.webp'}

    all_files = [f for f in downloaded_files if f.is_file()]
    video_files = [f for f in all_files if f.suffix.lower() in {'.mp4', '.webm', '.mkv'}]
    image_files = [f for f in all_files if f.suffix.lower() in image_exts]

    # Determine what to return based on content type
    if content_type in ['reel', 'video', 'tv', 'story']:
        if video_files:
            media_files = sorted(video_files)
        else:
            media_files = sorted(image_files)
    else:
        if video_files:
            media_files = sorted(video_files)
        else:
            media_files = sorted(image_files)

    if not media_files:
        return None, "No media files were downloaded."

    return media_files, None


def fetch_metadata(url, cookies_path, ytdlp_bin):
    """Fetch metadata using yt-dlp --dump-json."""
    cmd = [
        ytdlp_bin,
        '--cookies', cookies_path,
        '--dump-json',
        '--no-download',
        '--no-warnings',
        '--socket-timeout', '30',
        url
    ]

    log_debug(f"Fetching metadata for URL: {url[:50]}...")

    return_code, stdout, stderr = run_ytdlp(cmd, timeout=60)

    if return_code != 0:
        return None, (stderr + '\n' + stdout).strip()

    # Parse JSON output (might be multiple lines for carousel)
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

    # Return first entry as main info, with all entries for carousel
    main_info = entries[0].copy()
    if len(entries) > 1:
        main_info['entries'] = entries
        main_info['_type'] = 'playlist'

    return main_info, None


def try_with_cookie(url, download_path, cookie_path, ytdlp_bin, cookie_index):
    """Try to fetch and download with a specific cookie file."""
    cookie_name = os.path.basename(cookie_path)
    log_debug(f"Trying cookie #{cookie_index + 1}: {cookie_name}")

    # Verify cookie file exists and is not empty
    if not os.path.isfile(cookie_path):
        return None, f"Cookie file not found: {cookie_name}", "cookie_not_found", False

    try:
        file_size = os.path.getsize(cookie_path)
        if file_size == 0:
            return None, f"Cookie file is empty: {cookie_name}", "cookie_empty", False
        if file_size < 100:
            return None, f"Cookie file seems invalid: {cookie_name}", "cookie_invalid", True
    except OSError as e:
        return None, f"Cannot read cookie file: {str(e)}", "cookie_unreadable", True

    # Fetch metadata first
    info_dict, error_msg = fetch_metadata(url, cookie_path, ytdlp_bin)

    if error_msg:
        if is_permanent_error(error_msg):
            return None, error_msg, "permanent_error", False
        if is_cookie_error(error_msg):
            return None, error_msg, "cookie_error", True
        return None, error_msg, "unknown_error", True

    # Determine content type
    content_type = get_content_type(url, info_dict)
    log_debug(f"Detected content type: {content_type}")

    # Download media
    media_files, error_msg = download_media(
        url, download_path, cookie_path, ytdlp_bin, content_type
    )

    if error_msg:
        if is_permanent_error(error_msg):
            return None, error_msg, "permanent_error", False
        if is_cookie_error(error_msg):
            return None, error_msg, "cookie_error", True
        return None, error_msg, "download_error", True

    # Success!
    return {
        'info_dict': info_dict,
        'media_files': media_files,
        'content_type': content_type,
        'cookie_used': cookie_path
    }, None, None, False


def main():
    # Parse arguments
    if len(sys.argv) < 4:
        log_error(
            "Usage: python instagram_fetch.py <url> <download_path> <cookies_json> [yt_dlp_path]",
            "invalid_args"
        )

    url = sys.argv[1]
    download_path = sys.argv[2]
    cookies_json = sys.argv[3]
    ytdlp_input = sys.argv[4] if len(sys.argv) >= 5 else 'yt-dlp'

    # Validate URL
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

    log_debug(f"Found {len(cookie_files)} cookie file(s) to try")

    # Find yt-dlp binary
    ytdlp_bin = find_ytdlp_binary(ytdlp_input)

    if not ytdlp_bin:
        log_error(
            f"yt-dlp binary not found. Tried: {ytdlp_input}",
            "ytdlp_missing"
        )

    # Test yt-dlp is runnable
    return_code, stdout, stderr = run_ytdlp([ytdlp_bin, '--version'], timeout=15)
    if return_code != 0:
        log_error(
            f"yt-dlp failed to run: {stderr[:200]}",
            "ytdlp_crashed"
        )

    log_debug(f"yt-dlp version: {stdout.strip()}")

    # Try each cookie file sequentially
    last_error = None
    last_error_type = None
    cookies_tried = 0

    for idx, cookie_path in enumerate(cookie_files):
        cookies_tried += 1

        result, error_msg, error_type, should_retry = try_with_cookie(
            url, download_path, cookie_path, ytdlp_bin, idx
        )

        if result:
            # Success! Build response
            info_dict = result['info_dict']
            media_files = result['media_files']
            content_type = result['content_type']

            # Extract metadata
            username = info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user'))
            caption = info_dict.get('description', info_dict.get('title', ''))
            thumbnail = info_dict.get('thumbnail', '')

            # Build items array
            items = []
            for i, file_path in enumerate(media_files):
                ext = file_path.suffix.lower().lstrip('.')
                is_video = ext in ['mp4', 'webm', 'mkv']

                # Find thumbnail for this item
                thumb_path = None
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

            # Update content type if carousel
            if len(items) > 1:
                content_type = 'carousel'

            # Build response
            response = {
                "success": True,
                "type": content_type,
                "username": username,
                "caption": (caption[:500] if caption else ""),
                "thumbnail": thumbnail,
                "items": items,
                "cookies_tried": cookies_tried,
                "cookie_used": os.path.basename(cookie_path)
            }

            print(json.dumps(response))
            sys.exit(0)

        # Store last error
        last_error = error_msg
        last_error_type = error_type

        # Check if we should stop trying
        if not should_retry:
            log_debug(f"Permanent error, stopping: {error_msg[:100]}")
            break

        log_debug(f"Cookie #{idx + 1} failed, trying next...")

    # All cookies failed
    if last_error_type == "permanent_error":
        error_lower = (last_error or '').lower()
        if 'private' in error_lower:
            log_error("This content is from a private account.", "private_content", cookies_tried)
        elif 'not found' in error_lower or '404' in error_lower:
            log_error("This post has been removed or doesn't exist.", "not_found", cookies_tried)
        else:
            log_error(last_error[:300] if last_error else "Unknown permanent error", "permanent_error", cookies_tried)
    else:
        error_detail = last_error[:200] if last_error else 'Unknown'
        log_error(
            f"All {cookies_tried} cookie(s) failed. Please try again later.",
            "all_cookies_failed",
            cookies_tried
        )


if __name__ == "__main__":
    main()