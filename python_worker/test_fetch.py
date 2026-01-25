#!/usr/bin/env python3
"""
Test script for debugging Instagram fetch issues.
Tests both video and photo downloads.

Usage:
    cd python_worker
    python test_fetch.py
"""

import os
import sys
import json
import subprocess
import shutil
from pathlib import Path


def print_section(title):
    print("\n" + "=" * 60)
    print(f" {title}")
    print("=" * 60)


def check_environment():
    print_section("ENVIRONMENT CHECK")
    
    print(f"Python version: {sys.version}")
    print(f"Python executable: {sys.executable}")
    print(f"Current working directory: {os.getcwd()}")
    print(f"Script directory: {os.path.dirname(os.path.abspath(__file__))}")
    print(f"HOME: {os.environ.get('HOME', 'NOT SET')}")
    
    # Check if requests is installed
    try:
        import requests
        print(f"requests library: {requests.__version__}")
    except ImportError:
        print("requests library: NOT INSTALLED")
        print("  Install with: pip install requests")


def find_ytdlp():
    print_section("YT-DLP DETECTION")
    
    candidates = [
        'yt-dlp',
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
        shutil.which('yt-dlp'),
    ]
    
    if sys.platform == 'win32':
        candidates.extend([
            r'C:\yt-dlp\yt-dlp.exe',
            r'C:\yt-dlp\yt-dlp.EXE',
        ])
    
    found = None
    for candidate in candidates:
        if not candidate:
            continue
        
        print(f"\nChecking: {candidate}")
        
        if os.path.isfile(candidate):
            print(f"  ✓ File exists")
        else:
            print(f"  ✗ File does not exist")
            continue
        
        try:
            result = subprocess.run(
                [candidate, '--version'],
                capture_output=True,
                text=True,
                timeout=10
            )
            if result.returncode == 0:
                print(f"  ✓ Runs successfully")
                print(f"  Version: {result.stdout.strip()}")
                found = candidate
            else:
                print(f"  ✗ Failed to run: {result.stderr[:100]}")
        except Exception as e:
            print(f"  ✗ Exception: {e}")
    
    return found


def check_cookies():
    print_section("COOKIE FILES CHECK")
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    cookies_dir = os.path.join(script_dir, 'cookies')
    
    print(f"Cookies directory: {cookies_dir}")
    print(f"Directory exists: {os.path.isdir(cookies_dir)}")
    
    if not os.path.isdir(cookies_dir):
        print("✗ Cookies directory not found!")
        return []
    
    cookie_files = list(Path(cookies_dir).glob('*.txt'))
    print(f"Found {len(cookie_files)} cookie file(s)")
    
    valid_cookies = []
    for cf in cookie_files:
        print(f"\n  File: {cf.name}")
        print(f"    Exists: {cf.exists()}")
        print(f"    Readable: {os.access(cf, os.R_OK)}")
        
        try:
            size = cf.stat().st_size
            print(f"    Size: {size} bytes")
            
            if size > 50:
                valid_cookies.append(str(cf))
                print(f"    ✓ Valid")
            else:
                print(f"    ✗ Too small")
        except Exception as e:
            print(f"    ✗ Error: {e}")
    
    return valid_cookies


def test_video_download(ytdlp_bin, cookie_path, url):
    """Test video/reel download."""
    print_section(f"TESTING VIDEO DOWNLOAD")
    
    print(f"URL: {url}")
    print(f"Cookie: {os.path.basename(cookie_path)}")
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    download_path = os.path.join(script_dir, 'test_download')
    
    # Clean up previous test
    if os.path.exists(download_path):
        shutil.rmtree(download_path)
    os.makedirs(download_path)
    
    cmd = [
        ytdlp_bin,
        '--cookies', cookie_path,
        '--no-warnings',
        '--no-check-certificates',
        '-o', os.path.join(download_path, '%(id)s.%(ext)s'),
        '--merge-output-format', 'mp4',
        url
    ]
    
    print(f"\nExecuting yt-dlp...")
    
    try:
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=120)
        
        print(f"Return code: {result.returncode}")
        
        if result.stdout:
            print(f"STDOUT: {result.stdout[:300]}")
        if result.stderr:
            print(f"STDERR: {result.stderr[:300]}")
        
        files = list(Path(download_path).glob('*'))
        print(f"\nDownloaded files: {len(files)}")
        for f in files:
            print(f"  - {f.name} ({f.stat().st_size} bytes)")
        
        if result.returncode == 0 and files:
            print("\n✓ VIDEO DOWNLOAD SUCCESS")
            return True
        else:
            print("\n✗ VIDEO DOWNLOAD FAILED")
            return False
            
    except Exception as e:
        print(f"\n✗ Exception: {e}")
        return False


def test_photo_download(cookie_path, url):
    """Test photo download using requests."""
    print_section(f"TESTING PHOTO DOWNLOAD")
    
    print(f"URL: {url}")
    print(f"Cookie: {os.path.basename(cookie_path)}")
    
    try:
        import requests
    except ImportError:
        print("✗ requests library not installed!")
        print("Install with: pip install requests")
        return False
    
    # Parse cookies from file
    cookies = {}
    try:
        with open(cookie_path, 'r') as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith('#'):
                    continue
                parts = line.split('\t')
                if len(parts) >= 7:
                    domain, _, path, secure, expires, name, value = parts[:7]
                    if 'instagram.com' in domain:
                        cookies[name] = value
        print(f"Parsed {len(cookies)} cookies")
    except Exception as e:
        print(f"Error parsing cookies: {e}")
        return False
    
    # Fetch the page to find image URLs
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept': 'text/html,application/xhtml+xml',
    }
    
    print(f"\nFetching page...")
    
    try:
        session = requests.Session()
        session.cookies.update(cookies)
        response = session.get(url, headers=headers, timeout=30)
        
        print(f"Status: {response.status_code}")
        print(f"Content length: {len(response.text)}")
        
        # Look for image URLs in the response
        import re
        
        # Find display_url
        pattern = r'"display_url"\s*:\s*"([^"]+)"'
        matches = re.findall(pattern, response.text)
        
        image_urls = []
        for match in matches:
            decoded = match.replace('\\u0026', '&').replace('\\/', '/')
            if 'cdninstagram.com' in decoded and decoded not in image_urls:
                image_urls.append(decoded)
        
        print(f"Found {len(image_urls)} image URLs")
        
        if image_urls:
            # Try to download first image
            print(f"\nDownloading first image...")
            img_response = session.get(image_urls[0], headers={
                'User-Agent': headers['User-Agent'],
                'Referer': 'https://www.instagram.com/',
            }, timeout=30)
            
            script_dir = os.path.dirname(os.path.abspath(__file__))
            download_path = os.path.join(script_dir, 'test_download')
            os.makedirs(download_path, exist_ok=True)
            
            img_path = os.path.join(download_path, 'test_image.jpg')
            with open(img_path, 'wb') as f:
                f.write(img_response.content)
            
            size = os.path.getsize(img_path)
            print(f"Downloaded: {img_path} ({size} bytes)")
            
            if size > 1000:
                print("\n✓ PHOTO DOWNLOAD SUCCESS")
                return True
            else:
                print("\n✗ Downloaded file too small")
                return False
        else:
            print("\n✗ No image URLs found in page")
            return False
            
    except Exception as e:
        print(f"\n✗ Exception: {e}")
        import traceback
        traceback.print_exc()
        return False


def test_full_script(ytdlp_bin, cookies, url, content_type='video'):
    """Test the full instagram_fetch.py script."""
    print_section(f"TESTING FULL SCRIPT ({content_type.upper()})")
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    python_script = os.path.join(script_dir, 'instagram_fetch.py')
    download_path = os.path.join(script_dir, 'test_download')
    
    # Clean up
    if os.path.exists(download_path):
        shutil.rmtree(download_path)
    os.makedirs(download_path)
    
    cookies_json = json.dumps(cookies)
    
    cmd = [
        sys.executable,
        python_script,
        url,
        download_path,
        cookies_json,
        ytdlp_bin
    ]
    
    print(f"URL: {url}")
    print(f"Download path: {download_path}")
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=180,
            cwd=script_dir
        )
        
        print(f"\nReturn code: {result.returncode}")
        
        if result.stderr:
            print(f"\nDebug output:")
            for line in result.stderr.strip().split('\n')[-10:]:
                print(f"  {line}")
        
        if result.stdout:
            print(f"\nJSON output:")
            try:
                data = json.loads(result.stdout.strip().split('\n')[-1])
                print(json.dumps(data, indent=2)[:500])
                
                if data.get('success'):
                    print(f"\n✓ FULL SCRIPT SUCCESS")
                    print(f"  Type: {data.get('type')}")
                    print(f"  Items: {len(data.get('items', []))}")
                    return True
                else:
                    print(f"\n✗ Script returned error: {data.get('error')}")
                    return False
            except json.JSONDecodeError:
                print(result.stdout[:500])
        
        # Check downloaded files
        files = list(Path(download_path).glob('*'))
        print(f"\nDownloaded files: {len(files)}")
        for f in files:
            print(f"  - {f.name} ({f.stat().st_size} bytes)")
        
        return len(files) > 0
        
    except subprocess.TimeoutExpired:
        print("\n✗ TIMEOUT")
        return False
    except Exception as e:
        print(f"\n✗ Exception: {e}")
        return False


def main():
    print("\n" + "=" * 60)
    print(" INSTAGRAM DOWNLOADER DEBUG TOOL")
    print("=" * 60)
    
    check_environment()
    ytdlp_bin = find_ytdlp()
    
    if not ytdlp_bin:
        print("\n✗ FATAL: Could not find yt-dlp!")
        return
    
    cookies = check_cookies()
    if not cookies:
        print("\n✗ FATAL: No valid cookies found!")
        return
    
    print("\n" + "=" * 60)
    print(" TEST OPTIONS")
    print("=" * 60)
    print("\n1. Test video/reel download")
    print("2. Test photo download")
    print("3. Test both")
    print("4. Custom URL test")
    
    choice = input("\nEnter choice (1-4) [3]: ").strip() or "3"
    
    video_url = "https://www.instagram.com/reel/DTqYuzMkvNO/"
    photo_url = "https://www.instagram.com/p/DT4YMpcD0ZO/"
    
    if choice == "1":
        test_video_download(ytdlp_bin, cookies[0], video_url)
        test_full_script(ytdlp_bin, cookies, video_url, 'video')
    elif choice == "2":
        test_photo_download(cookies[0], photo_url)
        test_full_script(ytdlp_bin, cookies, photo_url, 'photo')
    elif choice == "3":
        print("\n--- Testing Video ---")
        test_full_script(ytdlp_bin, cookies, video_url, 'video')
        print("\n--- Testing Photo ---")
        test_full_script(ytdlp_bin, cookies, photo_url, 'photo')
    elif choice == "4":
        custom_url = input("Enter Instagram URL: ").strip()
        if custom_url:
            test_full_script(ytdlp_bin, cookies, custom_url, 'custom')
    
    print_section("DONE")
    print("\nCheck the test_download folder for downloaded files.")


if __name__ == "__main__":
    main()