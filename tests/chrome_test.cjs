const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

(async () => {
    console.log('Starting full GUI logins check...');
    
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
        // ================= STUDENT LOGIN =================
        console.log('1. Testing Student Login...');
        const studentCtx = await browser.createBrowserContext();
        const studentPage = await studentCtx.newPage();
        await studentPage.setViewport({ width: 1280, height: 800 });

        await studentPage.goto('http://localhost:8000/login', { waitUntil: 'networkidle2' });
        await studentPage.waitForSelector('#email', { timeout: 10000 });
        
        await studentPage.type('#email', 'student@thisai.com', { delay: 50 });
        await studentPage.type('#password', 'password', { delay: 50 });
        await sleep(500);
        
        await Promise.all([
            studentPage.click('button[type="submit"]'),
            studentPage.waitForNavigation({ waitUntil: 'networkidle2' }),
        ]);
        console.log('Student dashboard loaded successfully.');
        await studentPage.screenshot({ path: path.join(artifactsDir, 'dashboard_screenshot.png') });
        await studentCtx.close();
        await sleep(1500);

        // ================= ADMIN LOGIN =================
        console.log('2. Testing Admin Panel Login...');
        const adminCtx = await browser.createBrowserContext();
        const adminPage = await adminCtx.newPage();
        await adminPage.setViewport({ width: 1280, height: 800 });

        // Capture browser console logs
        adminPage.on('console', msg => console.log('Admin Browser Console:', msg.text()));

        await adminPage.goto('http://localhost:8000/admin/login', { waitUntil: 'networkidle2' });
        
        console.log('Waiting for Filament 4 Admin form inputs...');
        await adminPage.waitForSelector('[id="form.email"]', { timeout: 10000 });

        await adminPage.type('[id="form.email"]', 'admin@thisai.com', { delay: 50 });
        await adminPage.type('[id="form.password"]', 'password', { delay: 50 });
        await sleep(1000);

        console.log('Submitting Admin form...');
        await Promise.all([
            adminPage.click('button[type="submit"]'),
            adminPage.waitForNavigation({ waitUntil: 'networkidle2' }),
        ]);
        
        console.log('Admin Panel final URL:', adminPage.url());
        await adminPage.screenshot({ path: path.join(artifactsDir, 'admin_dashboard_screenshot.png') });
        console.log('Admin screenshot saved!');
        await adminCtx.close();
        await sleep(1500);

        // ================= FACULTY LOGIN =================
        console.log('3. Testing Faculty Panel Login...');
        const facultyCtx = await browser.createBrowserContext();
        const facultyPage = await facultyCtx.newPage();
        await facultyPage.setViewport({ width: 1280, height: 800 });

        // Capture browser console logs
        facultyPage.on('console', msg => console.log('Faculty Browser Console:', msg.text()));

        await facultyPage.goto('http://localhost:8000/faculty/login', { waitUntil: 'networkidle2' });
        
        console.log('Waiting for Filament 4 Faculty form inputs...');
        await facultyPage.waitForSelector('[id="form.email"]', { timeout: 10000 });

        await facultyPage.type('[id="form.email"]', 'faculty@thisai.com', { delay: 50 });
        await facultyPage.type('[id="form.password"]', 'password', { delay: 50 });
        await sleep(1000);

        console.log('Submitting Faculty form...');
        await Promise.all([
            facultyPage.click('button[type="submit"]'),
            facultyPage.waitForNavigation({ waitUntil: 'networkidle2' }),
        ]);
        
        console.log('Faculty Panel final URL:', facultyPage.url());
        await facultyPage.screenshot({ path: path.join(artifactsDir, 'faculty_dashboard_screenshot.png') });
        console.log('Faculty screenshot saved!');
        await facultyCtx.close();
        await sleep(1500);

    } catch (error) {
        console.error('Login check failed:', error);
    } finally {
        await browser.close();
        console.log('GUI login checks complete.');
    }
})();
