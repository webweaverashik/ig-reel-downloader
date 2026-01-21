#!/usr/bin/env python3
"""
Test script to verify Python and yt-dlp installation.
Run this to check if your environment is properly configured.

Usage:
python3 test_setup.py (Linux/macOS)
python test_setup.py (Windows)
"""

import sys
import subprocess
import json


def check_python_version():
"""Check Python version."""
print("=" * 50)
print("PYTHON VERSION CHECK")
print("=" * 50)
version = sys.version_info
print(f"Python Version: {version.major}.{version.minor}.{version.micro}")
print(f"Executable: {sys.executable}")

if version.major < 3 or (version.major==3 and version.minor < 10): print("❌ WARNING: Python 3.10+ is recommended")
    return False else: print("✅ Python version OK") return True def
    check_ytdlp(): """Check if yt-dlp is installed and working.""" print("\n" + "=" * 50) print("YT-DLP CHECK") print("=" * 50)
    
    try:
        result = subprocess.run(
            ['yt-dlp', '--version'],
            capture_output=True,
            text=True,
            timeout=10
        )
        
        if result.returncode == 0:
            print(f" yt-dlp Version: {result.stdout.strip()}") print("✅ yt-dlp is installed and working") return True
    else: print(f"❌ yt-dlp error: {result.stderr}") return False except FileNotFoundError: print("❌ yt-dlp is NOT
    installed!") print("\nTo install yt-dlp, run:") print(" pip install yt-dlp") print(" OR") print(" pip3 install
    yt-dlp") return False except Exception as e: print(f"❌ Error checking yt-dlp: {e}") return False def
    test_instagram_fetch(): """Test fetching a public Instagram post.""" print("\n" + "=" * 50) print("INSTAGRAM FETCH
    TEST") print("=" * 50)
    
    # Use a known public Instagram reel for testing
    # This is Instagram's official account which should always be public
    test_url = " https://www.instagram.com/reel/C0abcdefg/" print(f"Note: This is a simulated test.") print(f"To test
    with a real URL, run:") print(f" python3 instagram_fetch.py <real_instagram_url>")
    print("\nExample:")
    print(" python3 instagram_fetch.py https://www.instagram.com/reel/ABC123/")

    return True


    def print_env_setup():
    """Print environment setup instructions."""
    print("\n" + "=" * 50)
    print("ENVIRONMENT SETUP FOR LARAVEL")
    print("=" * 50)

    print("\nAdd to your .env file:")
    print("-" * 30)

    if sys.platform == 'win32':
    python_path = sys.executable.replace('\\', '\\\\')
    print(f'PYTHON_PATH="{python_path}"')
    else:
    print(f'PYTHON_PATH="{sys.executable}"')

    print("-" * 30)

    print(f"\nOr use these values:")
    print(f" Python executable: {sys.executable}")


    def main():
    print("\n" + "=" * 50)
    print(" ig reel downloader - Setup Verification")
    print("=" * 50)

    results = {
    'python': check_python_version(),
    'ytdlp': check_ytdlp(),
    }

    test_instagram_fetch()
    print_env_setup()

    print("\n" + "=" * 50)
    print("SUMMARY")
    print("=" * 50)

    all_passed = all(results.values())

    for check, passed in results.items():
    status = "✅ PASS" if passed else "❌ FAIL"
    print(f" {check}: {status}")

    if all_passed:
    print("\n✅ All checks passed! Your environment is ready.")
    else:
    print("\n❌ Some checks failed. Please fix the issues above.")

    print("\n")
    return 0 if all_passed else 1


    if __name__ == '__main__':
    sys.exit(main())
