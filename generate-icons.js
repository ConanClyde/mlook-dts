import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import sharp from 'sharp';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Read the SVG file
const svgPath = path.join(__dirname, 'public', 'images', 'logo.svg');

// Icon sizes needed for PWA and favicon
const sizes = [16, 32, 96, 192, 512];

// Generate icons
async function generateIcons() {
    console.log('Generating PWA icons from logo.svg...');
    
    for (const size of sizes) {
        const outputPath = path.join(__dirname, 'public', 'images', `icon-${size}x${size}.png`);
        
        try {
            await sharp(svgPath)
                .resize(size, size, {
                    fit: 'contain',
                    background: { r: 255, g: 255, b: 255, alpha: 0 }
                })
                .png()
                .toFile(outputPath);
            
            console.log(`✓ Generated ${size}x${size} icon`);
        } catch (error) {
            console.error(`✗ Error generating ${size}x${size} icon:`, error.message);
        }
    }
    
    console.log('Done! PWA icons generated successfully.');
}

generateIcons().catch(console.error);
