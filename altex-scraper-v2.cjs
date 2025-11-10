const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--disable-dev-shm-usage'
        ]
    });

    try {
        const page = await browser.newPage();
        
        // Set realistic user agent and headers
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        });
        
        // Hide webdriver property
        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', {
                get: () => false,
            });
        });

        console.error('Loading page...');
        
        await page.goto('https://altex.ro/aparate-frigorifice/cpl/', {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        console.error('Waiting for products...');
        
        // Wait for product grid
        try {
            await page.waitForSelector('.Products', { timeout: 15000 });
        } catch(e) {
            console.error('Products selector not found, trying alternative...');
            await page.waitForSelector('[class*="Product"]', { timeout: 15000 });
        }

        console.error('Scrolling page...');
        
        // Scroll gradually to load lazy images
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 200;
                const timer = setInterval(() => {
                    window.scrollBy(0, distance);
                    totalHeight += distance;

                    if(totalHeight >= document.body.scrollHeight - window.innerHeight){
                        clearInterval(timer);
                        setTimeout(resolve, 2000);
                    }
                }, 200);
            });
        });

        console.error('Extracting products...');

        // Extract product data directly
        const products = await page.evaluate(() => {
            const items = [];
            const productElements = document.querySelectorAll('[data-product-id]');
            
            productElements.forEach(el => {
                try {
                    const nameEl = el.querySelector('a[href*="/"]');
                    const priceEl = el.querySelector('[class*="Price-int"]');
                    const imageEl = el.querySelector('img');
                    
                    if (nameEl) {
                        items.push({
                            name: nameEl.textContent.trim(),
                            url: nameEl.href,
                            price: priceEl ? priceEl.textContent.trim() : null,
                            image: imageEl ? (imageEl.src || imageEl.dataset.src) : null,
                            productId: el.dataset.productId
                        });
                    }
                } catch(e) {}
            });
            
            return items;
        });

        console.log(JSON.stringify(products, null, 2));

    } catch (error) {
        console.error('Error:', error.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();
