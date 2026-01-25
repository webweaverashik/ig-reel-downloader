#!/usr/bin/env python3
"""
Test script for debugging Instagram fetch issues.
Run this directly from the command line to diagnose problems.

Usage:
    cd python_worker
    python test_fetch.py

Or from project root:
    python python_worker/test_fetch.py
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
    print(f"PATH: {os.environ.get('PATH', 'NOT SET')[:200]}...")


def find_ytdlp():
    print_section("YT-DLP DETECTION")
    
    candidates = [
        'yt-dlp',
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
        shutil.which('yt-dlp'),
    ]
    
    # Also check Windows paths
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
        
        # Check if file exists
        if os.path.isfile(candidate):
            print(f"  ✓ File exists")
        else:
            print(f"  ✗ File does not exist")
            continue
        
        # Try running it
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
        print(f"    Path: {cf}")
        print(f"    Exists: {cf.exists()}")
        print(f"    Readable: {os.access(cf, os.R_OK)}")
        
        try:
            size = cf.stat().st_size
            print(f"    Size: {size} bytes")
            
            if size > 50:
                valid_cookies.append(str(cf))
                print(f"    ✓ Valid")
            else:
                print(f"    ✗ Too small (< 50 bytes)")
        except Exception as e:
            print(f"    ✗ Error: {e}")
    
    return valid_cookies


def test_fetch(ytdlp_bin, cookie_path, url):
    print_section(f"TESTING FETCH WITH: {os.path.basename(cookie_path)}")
    
    print(f"URL: {url}")
    print(f"Cookie: {cookie_path}")
    print(f"yt-dlp: {ytdlp_bin}")
    
    cmd = [
        ytdlp_bin,
        '--cookies', cookie_path,
        '--dump-json',
        '--no-download',
        '--no-warnings',
        '--no-check-certificates',
        '--socket-timeout', '30',
        url
    ]
    
    print(f"\nCommand: {' '.join(cmd[:5])}...")
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=60
        )
        
        print(f"\nReturn code: {result.returncode}")
        
        if result.stdout:
            print(f"\nSTDOUT ({len(result.stdout)} chars):")
            print(result.stdout[:500])
            if len(result.stdout) > 500:
                print("...")
        
        if result.stderr:
            print(f"\nSTDERR ({len(result.stderr)} chars):")
            print(result.stderr[:500])
            if len(result.stderr) > 500:
                print("...")
        
        if result.returncode == 0:
            print("\n✓ SUCCESS - yt-dlp can fetch this content!")
            return True
        else:
            print("\n✗ FAILED - yt-dlp returned error")
            return False
            
    except subprocess.TimeoutExpired:
        print("\n✗ TIMEOUT - Command took too long")
        return False
    except Exception as e:
        print(f"\n✗ EXCEPTION: {e}")
        return False


def test_php_simulation(ytdlp_bin, cookies, url):
    """Simulate how PHP calls the Python script"""
    print_section("SIMULATING PHP CALL")
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    python_script = os.path.join(script_dir, 'instagram_fetch.py')
    download_path = os.path.join(script_dir, 'test_download')
    
    # Create download directory
    os.makedirs(download_path, exist_ok=True)
    
    cookies_json = json.dumps(cookies)
    
    cmd = [
        sys.executable,
        python_script,
        url,
        download_path,
        cookies_json,
        ytdlp_bin
    ]
    
    print(f"Command: python instagram_fetch.py <url> <path> <cookies> <ytdlp>")
    print(f"Python: {sys.executable}")
    print(f"Script: {python_script}")
    print(f"Download path: {download_path}")
    print(f"Cookies: {len(cookies)} file(s)")
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=120,
            cwd=script_dir
        )
        
        print(f"\nReturn code: {result.returncode}")
        
        if result.stdout:
            print(f"\nSTDOUT:")
            print(result.stdout)
        
        if result.stderr:
            print(f"\nSTDERR (debug messages):")
            print(result.stderr[:1000])
        
        # Try to parse JSON output
        for line in result.stdout.strip().split('\n'):
            try:
                data = json.loads(line)
                if data.get('success'):
                    print("\n✓ SUCCESS!")
                    return True
                elif data.get('error'):
                    print(f"\n✗ ERROR: {data.get('error')}")
                    if data.get('debug'):
                        print(f"Debug: {json.dumps(data.get('debug'), indent=2)}")
                    return False
            except json.JSONDecodeError:
                continue
        
        return False
        
    except subprocess.TimeoutExpired:
        print("\n✗ TIMEOUT")
        return False
    except Exception as e:
        print(f"\n✗ EXCEPTION: {e}")
        return False


def main():
    print("\n" + "=" * 60)
    print(" INSTAGRAM DOWNLOADER DEBUG TOOL")
    print("=" * 60)
    
    # Check environment
    check_environment()
    
    # Find yt-dlp
    ytdlp_bin = find_ytdlp()
    if not ytdlp_bin:
        print("\n✗ FATAL: Could not find a working yt-dlp binary!")
        print("Please install yt-dlp: pip install yt-dlp")
        return
    
    print(f"\n✓ Using yt-dlp: {ytdlp_bin}")
    
    # Check cookies
    cookies = check_cookies()
    if not cookies:
        print("\n✗ FATAL: No valid cookie files found!")
        print("Please add cookie files to: python_worker/cookies/")
        return
    
    print(f"\n✓ Found {len(cookies)} valid cookie(s)")
    
    # Test URL
    test_url = input("\nEnter an Instagram URL to test (or press Enter for default): ").strip()
    if not test_url:
        test_url = "https://www.instagram.com/reel/C5L5L5L5L5L/"
        print(f"Using test URL: {test_url}")
    
    # Test direct yt-dlp fetch
    for cookie in cookies:
        success = test_fetch(ytdlp_bin, cookie, test_url)
        if success:
            break
    
    # Test PHP simulation
    test_php_simulation(ytdlp_bin, cookies, test_url)
    
    print_section("SUMMARY")
    print("""
If yt-dlp works directly but fails through PHP:
1. Check file permissions on cookie files (chmod 644)
2. Check web server user can read the files
3. Check PHP's open_basedir restrictions
4. Check PHP's proc_open is allowed

If both fail:
1. Cookie may be expired - export fresh cookies
2. Instagram may have changed their API
3. Try updating yt-dlp: pip install -U yt-dlp
""")


if __name__ == "__main__":
    main()