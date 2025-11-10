#!/usr/bin/env node

/**
 * Puppeteer-based scraper for JavaScript-rendered websites
 * Usage: node scraper.js <url>
 * Returns: Full HTML after JavaScript execution
 */

const puppeteer = require('puppeteer');

async function scrapeUrl(url) {
    let browser;
    try {
        // Launch browser
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--disable-gpu'
            ]
        });

        const page = await browser.newPage();
        
        // Set user agent to avoid detection
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
        
        // Set viewport
        await page.setViewport({ width: 1920, height: 1080 });
        
        // Navigate to URL
        await page.goto(url, {
            waitUntil: 'networkidle0',
            timeout: 60000
        });
        
        // Wait for React to render specifications
        // Wait for the specs section to appear
        try {
            await page.waitForSelector('.specificationList, [class*="specification"], [data-cy="specsSectionsBtn"]', {
                timeout: 10000
            });
            console.error('Specs section found, waiting for content...');
        } catch (e) {
            console.error('Specs section selector not found');
        }
        
        // Additional wait for JavaScript to populate data
        await page.evaluate(() => {
            return new Promise((resolve) => {
                setTimeout(resolve, 5000);
            });
        });
        
        // Scroll to trigger lazy loading
        await page.evaluate(() => {
            window.scrollTo(0, document.body.scrollHeight / 2);
        });
        
        await page.evaluate(() => {
            return new Promise((resolve) => {
                setTimeout(resolve, 2000);
            });
        });
        
        // Get the full HTML
        const html = await page.content();
        
        // Output HTML to stdout
        console.log(html);
        
        await browser.close();
        process.exit(0);
        
    } catch (error) {
        if (browser) {
            await browser.close();
        }
        console.error('ERROR: ' + error.message);
        process.exit(1);
    }
}

// Get URL from command line argument
const url = process.argv[2];

if (!url) {
    console.error('ERROR: URL argument required');
    console.error('Usage: node scraper.js <url>');
    process.exit(1);
}

scrapeUrl(url);
