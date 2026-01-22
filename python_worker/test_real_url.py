#!/usr/bin/env python3
"""
Test script to debug Instagram URL fetching.
This will show exactly what yt-dlp returns.
"""

import subprocess
import sys
import json

def test_url(url):
    print("=" * 60)
    print("TESTING URL:", url)
    print("=" * 60)
    
    # Clean URL
    if '?' in url:
        url = url.split('?')[0]
    if not url.endswith('/'):
        url += '/'
    
    print(f"\nCleaned URL: {url}")
    
    # Find yt-dlp
    ytdlp_paths = ['yt-dlp', '/usr/local/bin/yt-dlp', '/usr/bin/yt-dlp']
    ytdlp = None
    
    for path in ytdlp_paths:
        try:
            result = subprocess.run([path, '--version'], capture_output=True, timeout=5)
            if result.returncode == 0:
                ytdlp = path
                print(f"\nFound yt-dlp: {path}")
                print(f"Version: {result.stdout.decode().strip()}")
                break
        except:
            continue
    
    if not ytdlp:
        print("\n[ERROR] yt-dlp not found!")
        return
    
    # Test 1: Simple dump-json
    print("\n" + "-" * 60)
    print("TEST 1: Basic yt-dlp --dump-json")
    print("-" * 60)
    
    cmd = [ytdlp, '--dump-json', '--skip-download', url]
    print(f"Command: {' '.join(cmd)}\n")
    
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
    
    print(f"Exit code: {result.returncode}")
    print(f"Stderr: {result.stderr[:500] if result.stderr else 'None'}")
    print(f"Stdout (first 500 chars): {result.stdout[:500] if result.stdout else 'None'}")
    
    # Test 2: With user agent
    print("\n" + "-" * 60)
    print("TEST 2: With User-Agent and headers")
    print("-" * 60)
    
    cmd = [
        ytdlp,
        '--dump-json',
        '--skip-download',
        '--no-warnings',
        '--user-agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        '--referer', 'https://www.instagram.com/',
        url
    ]
    print(f"Command: {' '.join(cmd[:5])} ... [with headers]\n")
    
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
    
    print(f"Exit code: {result.returncode}")
    print(f"Stderr: {result.stderr[:500] if result.stderr else 'None'}")
    
    if result.returncode == 0 and result.stdout:
        try:
            data = json.loads(result.stdout.split('\n')[0])
            print("\n[SUCCESS] Got data!")
            print(f"  Title: {data.get('title', 'N/A')[:50]}")
            print(f"  Uploader: {data.get('uploader', 'N/A')}")
            print(f"  Duration: {data.get('duration', 'N/A')}")
            print(f"  Formats: {len(data.get('formats', []))} available")
            if data.get('formats'):
                print("  First format URL (truncated):", data['formats'][0].get('url', '')[:80])
        except json.JSONDecodeError as e:
            print(f"\n[ERROR] JSON parse error: {e}")
            print(f"Raw stdout: {result.stdout[:300]}")
    else:
        print(f"Stdout: {result.stdout[:500] if result.stdout else 'None'}")
    
    # Test 3: List formats only
    print("\n" + "-" * 60)
    print("TEST 3: List available formats (-F)")
    print("-" * 60)
    
    cmd = [ytdlp, '-F', url]
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
    
    print(f"Exit code: {result.returncode}")
    if result.returncode == 0:
        print(f"Formats:\n{result.stdout[:1000]}")
    else:
        print(f"Error: {result.stderr[:500]}")


if __name__ == '__main__':
    if len(sys.argv) < 2:
        # Default test URL
        test_url("https://www.instagram.com/reel/DTz05EZE67L/")
    else:
        test_url(sys.argv[1])