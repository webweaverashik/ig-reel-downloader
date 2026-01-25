#!/usr/bin/env python3
"""
Instagram Downloader - Python Worker
IGReelDownloader.net

Cookie-based downloading using yt-dlp with multiple cookie support.
Supports: Reels, Videos, Photos, Stories, Carousel posts.

Usage:
    python instagram_fetch.py <instagram_url> <download_path> <cookies_json> [yt_dlp_path]
"""

import sys
import os
import json
import subprocess
import re
import shutil
from pathlib import Path


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


def find_ytdlp_binary(ytdlp_input):
    """Find a working yt-dlp binary."""
    candidates = []
    
    # Add the provided path first
    if ytdlp_input and ytdlp_input.strip():
        candidates.append(ytdlp_input.strip())
    
    # Add common locations
    candidates.extend([
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
        '/root/.local/bin/yt-dlp',
        'yt-dlp',
    ])
    
    # Add shutil.which result
    which_result = shutil.which('yt-dlp')
    if which_result:
        candidates.insert(1, which_result)
    
    # Remove duplicates while preserving order
    seen = set()
    unique_candidates = []
    for c in candidates:
        if c and c not in seen:
            seen.add(c)
            unique_candidates.append(c)
    
    log_debug(f"Searching for yt-dlp in: {unique_candidates}")
    
    for candidate in unique_candidates:
        if not candidate:
            continue
        try:
            # Check if it's a file that exists
            if os.path.isfile(candidate) and os.access(candidate, os.X_OK):
                log_debug(f"Found executable yt-dlp at: {candidate}")
                return candidate
            
            # Try running it
            result = subprocess.run(
                [candidate, '--version'],
                capture_output=True,
                text=True,
                timeout=10,
                env=get_env()
            )
            if result.returncode == 0:
                log_debug(f"Found working yt-dlp: {candidate} (version: {result.stdout.strip()})")
                return candidate
        except Exception as e:
            log_debug(f"yt-dlp candidate {candidate} failed: {e}")
            continue

    return None


def get_env():
    """Get environment variables for subprocess."""
    env = os.environ.copy()
    
    # Ensure HOME is set
    if 'HOME' not in env or not env['HOME']:
        env['HOME'] = '/tmp'
    
    # Ensure PATH includes common binary locations
    path_additions = ['/usr/local/bin', '/usr/bin', '/bin', '/home/ubuntu/.local/bin']
    current_path = env.get('PATH', '')
    for p in path_additions:
        if p not in current_path:
            current_path = p + ':' + current_path
    env['PATH'] = current_path
    
    return env


def run_ytdlp(args, timeout=120):
    """Run yt-dlp with proper error handling."""
    try:
        log_debug(f"Running: {' '.join(args[:6])}...")
        
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
    except PermissionError as e:
        return -4, '', f'Permission denied: {str(e)}'
    except Exception as e:
        return -5, '', f'Unexpected error: {str(e)}'


def fetch_metadata(url, cookies_path, ytdlp_bin):
    """Fetch metadata using yt-dlp --dump-json."""
    
    # Verify cookie file is readable
    if not os.path.isfile(cookies_path):
        return None, f"Cookie file does not exist: {cookies_path}"
    
    if not os.access(cookies_path, os.R_OK):
        return None, f"Cookie file is not readable: {cookies_path}"
    
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
        log_debug(f"Metadata fetch failed (code {return_code}): {combined[:300]}")
        return None, combined

    # Parse JSON output
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


def download_media(url, download_path, cookies_path, ytdlp_bin, content_type='post'):
    """Download media using yt-dlp."""
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
    ]

    if content_type in ['reel', 'video', 'tv', 'story']:
        cmd += [
            '--merge-output-format', 'mp4',
            '--write-thumbnail',
            '--convert-thumbnails', 'jpg'
        ]

    cmd.append(url)

    log_debug(f"Downloading media to: {download_path}")

    return_code, stdout, stderr = run_ytdlp(cmd, timeout=300)

    combined_output = (stdout + '\n' + stderr).strip()

    if return_code != 0:
        # Check if any files were downloaded despite error
        downloaded_files = list(Path(download_path).glob('*'))
        media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
        media_files = [f for f in downloaded_files if f.suffix.lower() in media_exts and f.is_file()]

        if media_files:
            log_debug(f"Found {len(media_files)} media files despite error")
            return sorted(media_files), None

        return None, combined_output

    # Find downloaded files
    downloaded_files = list(Path(download_path).glob('*'))

    media_exts = {'.mp4', '.webm', '.mkv', '.jpg', '.jpeg', '.png', '.webp'}
    image_exts = {'.jpg', '.jpeg', '.png', '.webp'}

    all_files = [f for f in downloaded_files if f.is_file()]
    video_files = [f for f in all_files if f.suffix.lower() in {'.mp4', '.webm', '.mkv'}]
    image_files = [f for f in all_files if f.suffix.lower() in image_exts]

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


def try_with_cookie(url, download_path, cookie_path, ytdlp_bin, cookie_index):
    """Try to fetch and download with a specific cookie file."""
    cookie_name = os.path.basename(cookie_path)
    log_debug(f"Trying cookie #{cookie_index + 1}: {cookie_name}")

    # Verify cookie file
    if not os.path.isfile(cookie_path):
        return None, f"Cookie file not found: {cookie_name}", "cookie_not_found", True

    try:
        file_size = os.path.getsize(cookie_path)
        if file_size == 0:
            return None, f"Cookie file is empty: {cookie_name}", "cookie_empty", True
        if file_size < 50:
            return None, f"Cookie file too small ({file_size} bytes): {cookie_name}", "cookie_invalid", True
        
        log_debug(f"Cookie file size: {file_size} bytes")
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
    ytdlp_input = sys.argv[4] if len(sys.argv) >= 5 else '/usr/local/bin/yt-dlp'

    log_debug(f"Script started")
    log_debug(f"URL: {url}")
    log_debug(f"Download path: {download_path}")
    log_debug(f"yt-dlp input: {ytdlp_input}")
    log_debug(f"Working directory: {os.getcwd()}")
    log_debug(f"User: {os.getenv('USER', 'unknown')}, UID: {os.getuid()}")

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

    log_debug(f"Cookie files to try: {len(cookie_files)}")
    for i, cf in enumerate(cookie_files):
        exists = os.path.isfile(cf)
        readable = os.access(cf, os.R_OK) if exists else False
        log_debug(f"  Cookie {i+1}: {cf} (exists: {exists}, readable: {readable})")

    # Find yt-dlp binary
    ytdlp_bin = find_ytdlp_binary(ytdlp_input)

    if not ytdlp_bin:
        log_error(
            f"yt-dlp binary not found. Tried: {ytdlp_input}",
            "ytdlp_missing",
            debug_info={"ytdlp_input": ytdlp_input, "cwd": os.getcwd(), "path": os.getenv('PATH', '')}
        )

    # Verify yt-dlp works
    return_code, stdout, stderr = run_ytdlp([ytdlp_bin, '--version'], timeout=15)
    if return_code != 0:
        log_error(
            f"yt-dlp failed to run: {stderr[:200]}",
            "ytdlp_crashed",
            debug_info={"ytdlp_bin": ytdlp_bin, "return_code": return_code, "stderr": stderr[:300]}
        )

    log_debug(f"yt-dlp version: {stdout.strip()}")

    # Try each cookie file
    last_error = None
    last_error_type = None
    cookies_tried = 0
    all_errors = []

    for idx, cookie_path in enumerate(cookie_files):
        cookies_tried += 1

        result, error_msg, error_type, should_retry = try_with_cookie(
            url, download_path, cookie_path, ytdlp_bin, idx
        )

        if result:
            # Success!
            info_dict = result['info_dict']
            media_files = result['media_files']
            content_type = result['content_type']

            username = info_dict.get('uploader', info_dict.get('uploader_id', 'instagram_user'))
            caption = info_dict.get('description', info_dict.get('title', ''))
            thumbnail = info_dict.get('thumbnail', '')

            items = []
            for i, file_path in enumerate(media_files):
                ext = file_path.suffix.lower().lstrip('.')
                is_video = ext in ['mp4', 'webm', 'mkv']

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

            if len(items) > 1:
                content_type = 'carousel'

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

        # Store error
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
        "cwd": os.getcwd()
    }

    if last_error_type == "permanent_error":
        error_lower = (last_error or '').lower()
        if 'private' in error_lower:
            log_error("This content is from a private account.", "private_content", cookies_tried, debug_info)
        elif 'not found' in error_lower or '404' in error_lower:
            log_error("This post has been removed or doesn't exist.", "not_found", cookies_tried, debug_info)
        else:
            log_error(last_error[:300] if last_error else "Unknown permanent error", "permanent_error", cookies_tried, debug_info)
    else:
        log_error(
            f"All {cookies_tried} cookie(s) failed. Please check cookie files and try again.",
            "all_cookies_failed",
            cookies_tried,
            debug_info
        )


if __name__ == "__main__":
    main()