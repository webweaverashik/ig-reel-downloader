#!/usr/bin/env python3
"""
Test script to verify Python and yt-dlp installation.
Run this to check if your environment is properly configured.

Usage: 
    python3 test_setup.py          (Linux/macOS)
    python test_setup.py           (Windows)
"""

import sys
import subprocess


def check_python_version():
    """Check Python version."""
    print("=" * 50)
    print("PYTHON VERSION CHECK")
    print("=" * 50)
    version = sys.version_info
    print(f"Python Version: {version.major}.{version.minor}.{version.micro}")
    print(f"Executable: {sys.executable}")
    
    if version.major < 3 or (version.major == 3 and version.minor < 8):
        print("[WARNING] Python 3.8+ is recommended")
        return False
    else:
        print("[OK] Python version is good")
        return True


def check_ytdlp():
    """Check if yt-dlp is installed and working."""
    print("")
    print("=" * 50)
    print("YT-DLP CHECK")
    print("=" * 50)
    
    try:
        result = subprocess.run(
            ['yt-dlp', '--version'],
            capture_output=True,
            text=True,
            timeout=10
        )
        
        if result.returncode == 0:
            print(f"yt-dlp Version: {result.stdout.strip()}")
            print("[OK] yt-dlp is installed and working")
            return True
        else:
            print(f"[ERROR] yt-dlp error: {result.stderr}")
            return False
            
    except FileNotFoundError:
        print("[ERROR] yt-dlp is NOT installed!")
        print("")
        print("To install yt-dlp, run:")
        print("  pip install yt-dlp")
        print("  OR")
        print("  pip3 install yt-dlp")
        return False
    except Exception as e:
        print(f"[ERROR] Error checking yt-dlp: {e}")
        return False


def test_instagram_fetch():
    """Test fetching a public Instagram post."""
    print("")
    print("=" * 50)
    print("INSTAGRAM FETCH TEST")
    print("=" * 50)
    
    print("Note: This is a simulated test.")
    print("To test with a real URL, run:")
    print("  python3 instagram_fetch.py <real_instagram_url>")
    print("")
    print("Example:")
    print("  python3 instagram_fetch.py https://www.instagram.com/reel/ABC123/")
    
    return True


def print_env_setup():
    """Print environment setup instructions."""
    print("")
    print("=" * 50)
    print("ENVIRONMENT SETUP FOR LARAVEL")
    print("=" * 50)
    
    print("")
    print("Add this to your .env file:")
    print("-" * 30)
    
    if sys.platform == 'win32':
        python_path = sys.executable.replace('\\', '\\\\')
        print(f'PYTHON_PATH="{sys.executable}"')
    else:
        print(f'PYTHON_PATH="{sys.executable}"')
    
    print("-" * 30)
    
    print("")
    print(f"Python executable path: {sys.executable}")


def main():
    print("")
    print("=" * 50)
    print("  ig reel downloader - Setup Verification")
    print("=" * 50)
    
    python_ok = check_python_version()
    ytdlp_ok = check_ytdlp()
    
    test_instagram_fetch()
    print_env_setup()
    
    print("")
    print("=" * 50)
    print("SUMMARY")
    print("=" * 50)
    
    print(f"  Python: {'[OK]' if python_ok else '[FAIL]'}")
    print(f"  yt-dlp: {'[OK]' if ytdlp_ok else '[FAIL]'}")
    
    if python_ok and ytdlp_ok:
        print("")
        print("[SUCCESS] All checks passed! Your environment is ready.")
    else:
        print("")
        print("[FAILED] Some checks failed. Please fix the issues above.")
    
    print("")
    return 0 if (python_ok and ytdlp_ok) else 1


if __name__ == '__main__':
    sys.exit(main())