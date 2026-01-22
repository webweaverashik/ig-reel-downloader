#!/bin/bash
#
# ig reel downloader - yt-dlp Installation Script for Ubuntu 24.04 VPS
# This script installs/upgrades yt-dlp using pipx (recommended for CLI tools)
#

echo "=================================================="
echo "  ig reel downloader - yt-dlp Installer"
echo "=================================================="
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo "[INFO] Running as root"
    USE_SUDO=""
else
    echo "[INFO] Running as regular user, will use sudo where needed"
    USE_SUDO="sudo"
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

echo ""
echo "Step 1: Installing pipx..."
echo "-----------------------------------"

if command_exists pipx; then
    echo "[OK] pipx is already installed"
else
    $USE_SUDO apt update
    $USE_SUDO apt install -y pipx python3-full
    pipx ensurepath
    echo "[OK] pipx installed"
fi

echo ""
echo "Step 2: Installing/Upgrading yt-dlp via pipx..."
echo "-----------------------------------"

if pipx list | grep -q "yt-dlp"; then
    echo "[INFO] yt-dlp found, upgrading..."
    pipx upgrade yt-dlp
else
    echo "[INFO] Installing yt-dlp..."
    pipx install yt-dlp
fi

echo ""
echo "Step 3: Verifying installation..."
echo "-----------------------------------"

# Ensure pipx bin is in PATH for this session
export PATH="$HOME/.local/bin:$PATH"

if command_exists yt-dlp; then
    echo "[OK] yt-dlp found at: $(which yt-dlp)"
    echo "[OK] yt-dlp version: $(yt-dlp --version)"
else
    echo "[WARNING] yt-dlp not in PATH, trying alternative..."
    
    # Try to find it
    if [ -f "$HOME/.local/bin/yt-dlp" ]; then
        echo "[OK] Found at: $HOME/.local/bin/yt-dlp"
        echo "[OK] Version: $($HOME/.local/bin/yt-dlp --version)"
        echo ""
        echo "[ACTION REQUIRED] Add this to your shell profile (~/.bashrc or ~/.profile):"
        echo '    export PATH="$HOME/.local/bin:$PATH"'
        echo ""
        echo "Or run:"
        echo '    echo "export PATH=\"\$HOME/.local/bin:\$PATH\"" >> ~/.bashrc && source ~/.bashrc'
    fi
fi

echo ""
echo "Step 4: Creating symlink for system-wide access..."
echo "-----------------------------------"

if [ -f "$HOME/.local/bin/yt-dlp" ]; then
    $USE_SUDO ln -sf "$HOME/.local/bin/yt-dlp" /usr/local/bin/yt-dlp 2>/dev/null || true
    if [ -f "/usr/local/bin/yt-dlp" ]; then
        echo "[OK] Symlink created: /usr/local/bin/yt-dlp"
    fi
fi

echo ""
echo "=================================================="
echo "  Installation Complete!"
echo "=================================================="
echo ""
echo "Test with:"
echo "  yt-dlp --version"
echo ""
echo "If yt-dlp is not found, run:"
echo "  source ~/.bashrc"
echo ""
echo "Or log out and log back in."
echo ""