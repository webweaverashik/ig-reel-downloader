#!/usr/bin/env python3
"""
Test script for debugging Instagram fetch issues.
Tests video, single photo, and carousel downloads.

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
        print(f"✓ requests library: {requests.__version__}")
    except ImportError:
        print("✗ requests library: NOT INSTALLED")
        print("  Install with: pip install requests")
        return False
    
    return True


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
                break
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


def test_carousel_extraction(cookies, url):
    """Test carousel image extraction directly."""
    print_section("TESTING CAROUSEL EXTRACTION")
    
    print(f"URL: {url}")
    
    try:
        from instagram_fetch import (
            parse_netscape_cookies, 
            extract_post_images_from_page, 
            extract_shortcode,
            find_carousel_media
        )
    except ImportError as e:
        print(f"✗ Could not import from instagram_fetch.py: {e}")
        return False
    
    try:
        import requests
    except ImportError:
        print("✗ requests library not installed!")
        return False
    
    # Parse cookies
    cookies_dict = parse_netscape_cookies(cookies[0])
    print(f"Parsed {len(cookies_dict)} cookies")
    
    # Extract shortcode
    shortcode = extract_shortcode(url)
    print(f"Shortcode: {shortcode}")
    
    # Fetch the page
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
    }
    
    session = requests.Session()
    session.cookies.update(cookies_dict)
    response = session.get(url, headers=headers, timeout=30)
    html = response.text
    
    print(f"Page HTML length: {len(html)}")
    
    # Check for carousel indicators
    has_sidecar = '"edge_sidecar_to_children"' in html
    has_carousel_media = '"carousel_media"' in html
    has_graph_sidecar = '"GraphSidecar"' in html
    
    print(f"\nCarousel indicators in HTML:")
    print(f"  edge_sidecar_to_children: {has_sidecar}")
    print(f"  carousel_media: {has_carousel_media}")
    print(f"  GraphSidecar: {has_graph_sidecar}")
    
    # Try carousel extraction
    if has_sidecar or has_carousel_media or has_graph_sidecar:
        print("\n  → Carousel detected, extracting images...")
        carousel_images = find_carousel_media(html, shortcode)
        print(f"  Found {len(carousel_images)} carousel images")
        for i, img in enumerate(carousel_images):
            print(f"    {i+1}. {img[:70]}...")
    else:
        print("\n  → No carousel indicators found")
    
    # Full extraction
    print(f"\nFull extraction result:")
    post_data = extract_post_images_from_page(url, cookies_dict, shortcode)
    
    image_urls = post_data.get('image_urls', [])
    username = post_data.get('username', 'unknown')
    is_carousel = post_data.get('is_carousel', False)
    
    print(f"  Username: {username}")
    print(f"  Is carousel: {is_carousel}")
    print(f"  Total images: {len(image_urls)}")
    
    for i, img in enumerate(image_urls):
        print(f"    {i+1}. {img[:70]}...")
    
    return len(image_urls) > 0


def test_full_script(ytdlp_bin, cookies, url, expected_type='auto'):
    """Test the full instagram_fetch.py script."""
    print_section(f"TESTING FULL SCRIPT ({expected_type.upper()})")
    
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
            print(f"\nDebug output (last 20 lines):")
            lines = result.stderr.strip().split('\n')
            for line in lines[-20:]:
                print(f"  {line}")
        
        if result.stdout:
            print(f"\nJSON output:")
            try:
                for line in result.stdout.strip().split('\n'):
                    line = line.strip()
                    if line.startswith('{'):
                        data = json.loads(line)
                        
                        print(f"\n  Success: {data.get('success')}")
                        print(f"  Type: {data.get('type')}")
                        print(f"  Username: {data.get('username')}")
                        print(f"  Items: {len(data.get('items', []))}")
                        
                        for item in data.get('items', []):
                            print(f"    - {item.get('filename')} ({item.get('type')}, {item.get('format')})")
                        
                        if data.get('success'):
                            item_count = len(data.get('items', []))
                            detected_type = data.get('type')
                            
                            if expected_type == 'carousel' and item_count <= 1:
                                print(f"\n⚠ WARNING: Expected carousel but got {item_count} item(s)")
                            elif expected_type == 'photo' and item_count > 1:
                                print(f"\n⚠ WARNING: Expected single photo but got {item_count} item(s)")
                            else:
                                print(f"\n✓ SUCCESS: Downloaded {item_count} item(s) as {detected_type}")
                            
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
            print(f"  - {f.name} ({f.stat().st_size:,} bytes)")
        
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
    
    if not check_environment():
        print("\n✗ Environment check failed. Please install missing dependencies.")
        return
    
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
    print("4. Test all types")
    print("5. Test carousel extraction only (debug)")
    print("6. Custom URL test")
    
    choice = input("\nEnter choice (1-6) [4]: ").strip() or "4"
    
    # Test URLs - use known working examples
    video_url = "https://www.instagram.com/reel/DR94FUYDOYH/"
    single_photo_url = "https://www.instagram.com/p/DT4YMpcD0ZO/"  # Single image post
    carousel_url = "https://www.instagram.com/p/DT7mBohAds4/"  # Multi-image carousel
    
    if choice == "1":
        test_full_script(ytdlp_bin, cookies, video_url, 'reel')
    
    elif choice == "2":
        test_full_script(ytdlp_bin, cookies, single_photo_url, 'photo')
    
    elif choice == "3":
        print("\n--- Testing Carousel Extraction Debug ---")
        test_carousel_extraction(cookies, carousel_url)
        print("\n--- Testing Full Script with Carousel ---")
        test_full_script(ytdlp_bin, cookies, carousel_url, 'carousel')
    
    elif choice == "4":
        results = {}
        
        print("\n" + "=" * 60)
        print(" TESTING VIDEO/REEL")
        print("=" * 60)
        results['video'] = test_full_script(ytdlp_bin, cookies, video_url, 'reel')
        
        print("\n" + "=" * 60)
        print(" TESTING SINGLE PHOTO")
        print("=" * 60)
        results['photo'] = test_full_script(ytdlp_bin, cookies, single_photo_url, 'photo')
        
        print("\n" + "=" * 60)
        print(" TESTING CAROUSEL")
        print("=" * 60)
        results['carousel'] = test_full_script(ytdlp_bin, cookies, carousel_url, 'carousel')
        
        print("\n" + "=" * 60)
        print(" SUMMARY")
        print("=" * 60)
        for test_type, success in results.items():
            status = "✓ PASS" if success else "✗ FAIL"
            print(f"  {test_type}: {status}")
    
    elif choice == "5":
        custom_url = input("Enter carousel URL [default test URL]: ").strip()
        if not custom_url:
            custom_url = carousel_url
        test_carousel_extraction(cookies, custom_url)
    
    elif choice == "6":
        custom_url = input("Enter Instagram URL: ").strip()
        if custom_url:
            # Determine expected type from URL
            if '/reel/' in custom_url.lower():
                expected = 'reel'
            else:
                expected = 'auto'
            
            print("\n--- Testing Extraction ---")
            test_carousel_extraction(cookies, custom_url)
            print("\n--- Testing Full Script ---")
            test_full_script(ytdlp_bin, cookies, custom_url, expected)
    
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