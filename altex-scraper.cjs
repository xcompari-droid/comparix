const puppeteer = require('puppeteer');

(async () => {
    const url = process.argv[2];
    
    if (!url) {
        console.error('Usage: node altex-scraper.cjs <url>');
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();
        
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        await page.goto(url, {
            waitUntil: 'networkidle0',
            timeout: 60000
        });

        // Wait for products to load
        await page.waitForSelector('.Products', { timeout: 10000 }).catch(() => {});
        
        // Scroll to trigger lazy loading
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 100;
                const timer = setInterval(() => {
                    const scrollHeight = document.body.scrollHeight;
                    window.scrollBy(0, distance);
                    totalHeight += distance;

                    if(totalHeight >= scrollHeight){
                        clearInterval(timer);
                        resolve();
                    }
                }, 100);
            });
        });

        // Wait for all images and content to load
        await page.evaluate(() => new Promise(resolve => setTimeout(resolve, 3000)));

        const html = await page.content();
        console.log(html);

    } catch (error) {
        console.error('Error:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();
