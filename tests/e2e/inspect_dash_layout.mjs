import puppeteer from 'puppeteer-core';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900 });
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

const res = await page.evaluate(() => {
    const pw = document.getElementById('page-wrapper');
    const drawer = document.getElementById('theme_boost-drawers-blocks');
    const pageEl = document.getElementById('page');
    const topOfScroll = document.getElementById('topofscroll');
    const regionMainBox = document.getElementById('region-main-box');
    const regionMain = document.getElementById('region-main');
    const contentSec = document.querySelector('section[data-blockregion="content"]');
    const sidePreAside = document.querySelector('aside[data-blockregion="side-pre"]');
    const calBlock = document.querySelector('.block_calendar_month');

    return {
        pwDisplay: window.getComputedStyle(pw).display,
        drawer: {
            display: window.getComputedStyle(drawer).display,
            position: window.getComputedStyle(drawer).position,
            width: drawer.offsetWidth,
            height: drawer.offsetHeight,
            classes: drawer.className
        },
        pageEl: {
            width: pageEl.offsetWidth,
            marginRight: window.getComputedStyle(pageEl).marginRight,
            classes: pageEl.className
        },
        regionMain: {
            width: regionMain.offsetWidth
        },
        contentSec: {
            width: contentSec.offsetWidth,
            maxWidth: window.getComputedStyle(contentSec).maxWidth,
            flex: window.getComputedStyle(contentSec).flex
        },
        sidePreAside: {
            width: sidePreAside?.offsetWidth,
            maxWidth: sidePreAside ? window.getComputedStyle(sidePreAside).maxWidth : null,
            flex: sidePreAside ? window.getComputedStyle(sidePreAside).flex : null
        },
        calBlock: {
            width: calBlock?.offsetWidth,
            htmlSnippet: calBlock?.outerHTML.substring(0, 300)
        }
    };
});

console.log(JSON.stringify(res, null, 2));
await browser.close();
