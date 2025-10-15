#!/bin/bash

# Power Reservations - Deployment Zip Creator
# Creates a properly structured WordPress plugin zip file

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}Power Reservations - Zip Creator${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Get version from power-reservations.php
VERSION=$(grep "Version:" power-reservations.php | head -1 | awk '{print $3}')
echo -e "${GREEN}Current version: ${VERSION}${NC}"

# Set variables
PLUGIN_NAME="power-reservations"
ZIP_NAME="${PLUGIN_NAME}-v${VERSION}.zip"
TEMP_DIR="temp-plugin-build"

echo -e "${BLUE}Creating deployment package...${NC}"

# Clean up old temp directory if it exists
if [ -d "$TEMP_DIR" ]; then
    rm -rf "$TEMP_DIR"
fi

# Create temp directory structure
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# Copy files to temp directory
echo -e "Copying files..."
cp power-reservations.php "$TEMP_DIR/$PLUGIN_NAME/"
cp readme.txt "$TEMP_DIR/$PLUGIN_NAME/"
cp uninstall.php "$TEMP_DIR/$PLUGIN_NAME/"
cp -r assets "$TEMP_DIR/$PLUGIN_NAME/"
cp -r includes "$TEMP_DIR/$PLUGIN_NAME/"

# Remove old zip if it exists
if [ -f "$ZIP_NAME" ]; then
    rm -f "$ZIP_NAME"
    echo -e "Removed old $ZIP_NAME"
fi

# Create zip from temp directory
cd "$TEMP_DIR"
zip -r "../$ZIP_NAME" "$PLUGIN_NAME/" -x "*.DS_Store" -x "*__MACOSX*" > /dev/null 2>&1
cd ..

# Clean up temp directory
rm -rf "$TEMP_DIR"

# Check if zip was created successfully
if [ -f "$ZIP_NAME" ]; then
    FILE_SIZE=$(ls -lh "$ZIP_NAME" | awk '{print $5}')
    echo -e "${GREEN}✅ Success!${NC}"
    echo -e "${GREEN}Created: ${ZIP_NAME} (${FILE_SIZE})${NC}"
    echo ""
    echo -e "${BLUE}Verifying structure...${NC}"
    unzip -l "$ZIP_NAME" | head -15
    echo ""
    echo -e "${GREEN}✅ Zip file is ready for deployment!${NC}"
else
    echo -e "${RED}❌ Error: Failed to create zip file${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}======================================${NC}"
echo -e "${GREEN}Deployment package ready!${NC}"
echo -e "${BLUE}======================================${NC}"
