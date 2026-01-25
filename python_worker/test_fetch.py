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
    
    # Import the extraction function from our main script
    try:
        from instagram_fetch import (
            parse_netscape_cookies, 
            extract_post_images_from_page, 
            extract_shortcode,
            download_image_with_requests
        )
    except ImportError:
        print("✗ Could not import from instagram_fetch.py")
        return False
    
    # Parse cookies from file
    cookies = parse_netscape_cookies(cookie_path)
    print(f"Parsed {len(cookies)} cookies")
    
    # Extract shortcode
    shortcode = extract_shortcode(url)
    print(f"Shortcode: {shortcode}")
    
    # Extract images
    print(f"\nExtracting images from page...")
    post_data = extract_post_images_from_page(url, cookies, shortcode)
    
    image_urls = post_data.get('image_urls', [])
    username = post_data.get('username', 'unknown')
    caption = post_data.get('caption', '')
    
    print(f"\nExtracted data:")
    print(f"  Username: {username}")
    print(f"  Caption: {caption[:100] if caption else 'None'}...")
    print(f"  Image URLs found: {len(image_urls)}")
    
    for i, img_url in enumerate(image_urls):
        print(f"    {i+1}. {img_url[:80]}...")
    
    if not image_urls:
        print("\n✗ No image URLs found")
        return False
    
    # Download first image
    script_dir = os.path.dirname(os.path.abspath(__file__))
    download_path = os.path.join(script_dir, 'test_download')
    os.makedirs(download_path, exist_ok=True)
    
    print(f"\nDownloading {len(image_urls)} image(s)...")
    
    downloaded = 0
    for idx, img_url in enumerate(image_urls):
        img_path = os.path.join(download_path, f'{shortcode}_{idx+1}.jpg')
        if download_image_with_requests(img_url, img_path, cookies):
            size = os.path.getsize(img_path)
            print(f"  ✓ Downloaded image {idx+1}: {size} bytes")
            downloaded += 1
        else:
            print(f"  ✗ Failed to download image {idx+1}")
    
    if downloaded > 0:
        print(f"\n✓ PHOTO DOWNLOAD SUCCESS ({downloaded}/{len(image_urls)} images)")
        return True
    else:
        print("\n✗ PHOTO DOWNLOAD FAILED")
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
            for line in result.stderr.strip().split('\n')[-15:]:
                print(f"  {line}")
        
        if result.stdout:
            print(f"\nJSON output:")
            try:
                # Find the JSON line in output
                for line in result.stdout.strip().split('\n'):
                    line = line.strip()
                    if line.startswith('{'):
                        data = json.loads(line)
                        print(json.dumps(data, indent=2)[:800])
                        
                        if data.get('success'):
                            print(f"\n✓ FULL SCRIPT SUCCESS")
                            print(f"  Type: {data.get('type')}")
                            print(f"  Username: {data.get('username')}")
                            print(f"  Items: {len(data.get('items', []))}")
                            
                            for item in data.get('items', []):
                                print(f"    - {item.get('filename')} ({item.get('type')}, {item.get('format')})")
                            
                            return True
                        else:
                            print(f"\n✗ Script returned error: {data.get('error')}")
                            return False
            except json.JSONDecodeError as e:
                print(f"JSON parse error: {e}")
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
        import traceback
        traceback.print_exc()
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
    print("2. Test single photo download")
    print("3. Test carousel (multi-photo) download")
    print("4. Test all (video + single photo + carousel)")
    print("5. Custom URL test")
    
    choice = input("\nEnter choice (1-5) [4]: ").strip() or "4"
    
    # Test URLs
    video_url = "https://www.instagram.com/reel/DR94FUYDOYH/"
    single_photo_url = "https://www.instagram.com/p/DT4YMpcD0ZO/"  # Single image post
    carousel_url = "https://www.instagram.com/p/DT7mBohAds4/"  # Multi-image carousel
    
    if choice == "1":
        test_full_script(ytdlp_bin, cookies, video_url, 'video')
    elif choice == "2":
        print("\n--- Testing Single Photo ---")
        test_photo_download(cookies[0], single_photo_url)
        print("\n--- Testing Full Script with Single Photo ---")
        test_full_script(ytdlp_bin, cookies, single_photo_url, 'photo')
    elif choice == "3":
        print("\n--- Testing Carousel ---")
        test_photo_download(cookies[0], carousel_url)
        print("\n--- Testing Full Script with Carousel ---")
        test_full_script(ytdlp_bin, cookies, carousel_url, 'carousel')
    elif choice == "4":
        print("\n--- Testing Video ---")
        test_full_script(ytdlp_bin, cookies, video_url, 'video')
        print("\n--- Testing Single Photo ---")
        test_full_script(ytdlp_bin, cookies, single_photo_url, 'photo')
        print("\n--- Testing Carousel ---")
        test_full_script(ytdlp_bin, cookies, carousel_url, 'carousel')
    elif choice == "5":
        custom_url = input("Enter Instagram URL: ").strip()
        if custom_url:
            # Determine type from URL
            if '/reel/' in custom_url.lower():
                test_full_script(ytdlp_bin, cookies, custom_url, 'reel')
            else:
                # Try photo extraction first
                print("\n--- Testing Photo Extraction ---")
                test_photo_download(cookies[0], custom_url)
                print("\n--- Testing Full Script ---")
                test_full_script(ytdlp_bin, cookies, custom_url, 'custom')
    
    print_section("DONE")
    print("\nCheck the test_download folder for downloaded files.")
    
    # List files
    script_dir = os.path.dirname(os.path.abspath(__file__))
    download_path = os.path.join(script_dir, 'test_download')
    if os.path.exists(download_path):
        files = list(Path(download_path).glob('*'))
        if files:
            print(f"\nFiles in test_download:")
            for f in files:
                print(f"  - {f.name} ({f.stat().st_size:,} bytes)")


if __name__ == "__main__":
    main()