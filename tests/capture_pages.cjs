const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

(async () => {
    console.log('Starting full page screenshots capture...');
    
    const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
    if (!fs.existsSync(chromePath)) {
        console.error('Google Chrome was not found at: ' + chromePath);
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: false,
        defaultViewport: null,
        args: ['--start-maximized']
    });

    const artifactsDir = '/Users/admin/.gemini/antigravity/brain/f7a218f3-88ac-4af3-af2f-c4141835c2c2';

    try {
        const studentCtx = await browser.createBrowserContext();
        const studentPage = await studentCtx.newPage();
        await studentPage.setViewport({ width: 1280, height: 800 });

        console.log('Logging in...');
        await studentPage.goto('http://localhost:8000/login', { waitUntil: 'networkidle2' });
        await studentPage.waitForSelector('#email', { timeout: 10000 });
        
        await studentPage.type('#email', 'student@thisai.com', { delay: 50 });
        await studentPage.type('#password', 'password', { delay: 50 });
        await sleep(500);
        
        await studentPage.click('button[type="submit"]');
        await studentPage.waitForNavigation({ waitUntil: 'networkidle2' });
        console.log('Logged in successfully. Taking dashboard screenshot...');
        await studentPage.screenshot({ path: path.join(artifactsDir, 'dashboard_screenshot.png') });

        console.log('Navigating to Current Affairs...');
        await studentPage.goto('http://localhost:8000/current-affairs', { waitUntil: 'networkidle2' });
        await sleep(1000);
        await studentPage.screenshot({ path: path.join(artifactsDir, 'current_affairs_screenshot.png') });

        console.log('Navigating to Rankings Leaderboard...');
        await studentPage.goto('http://localhost:8000/leaderboard', { waitUntil: 'networkidle2' });
        await sleep(1000);
        await studentPage.screenshot({ path: path.join(artifactsDir, 'leaderboard_screenshot.png') });

        await studentCtx.close();
        console.log('All screenshots captured successfully!');
    } catch (error) {
        console.error('Capture failed:', error);
    } finally {
        await browser.close();
    }
})();
