/**
 * Concurrent Login Test - Simulates N students logging in simultaneously
 * Uses proper cookie handling and CSRF token extraction
 */
const https = require('https');
const http = require('http');
const { URL } = require('url');

const BASE_URL = 'https://sdnug09.app';
const CONCURRENT_USERS = parseInt(process.argv[2]) || 50;
const LOGIN_PATH = '/simulasi/login';
const POST_PATH = '/simulasi/student-login';

function request(url, options = {}) {
  return new Promise((resolve, reject) => {
    const parsed = new URL(url);
    const mod = parsed.protocol === 'https:' ? https : http;
    const req = mod.request(parsed, {
      method: options.method || 'GET',
      headers: options.headers || {},
      timeout: 30000,
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        const cookies = (res.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
        resolve({ status: res.statusCode, body, cookies, headers: res.headers });
      });
    });
    req.on('error', reject);
    req.on('timeout', () => { req.destroy(); reject(new Error('timeout')); });
    if (options.body) req.write(options.body);
    req.end();
  });
}

async function simulateLogin(userId) {
  const start = Date.now();
  try {
    // Step 1: GET login page (get session cookie + CSRF token)
    const getRes = await request(`${BASE_URL}${LOGIN_PATH}`);
    const tokenMatch = getRes.body.match(/name="_token"\s+value="([^"]+)"/);
    if (!tokenMatch) {
      return { userId, status: 'FAIL', error: 'No CSRF token found', time: Date.now() - start };
    }
    const csrfToken = tokenMatch[1];
    const sessionCookie = getRes.cookies;

    // Step 2: POST login with CSRF token + session cookie
    const postBody = `_token=${encodeURIComponent(csrfToken)}&nisn=test${String(userId).padStart(4,'0')}&password=testpass`;
    const postRes = await request(`${BASE_URL}${POST_PATH}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cookie': sessionCookie,
      },
      body: postBody,
    });

    return {
      userId,
      status: postRes.status === 302 ? 'OK' : (postRes.status === 419 ? 'CSRF_FAIL' : `HTTP_${postRes.status}`),
      httpCode: postRes.status,
      time: Date.now() - start,
    };
  } catch (err) {
    return { userId, status: 'ERROR', error: err.message, time: Date.now() - start };
  }
}

async function runTest() {
  console.log(`\n=== Concurrent Login Test ===`);
  console.log(`Users: ${CONCURRENT_USERS}`);
  console.log(`Target: ${BASE_URL}`);
  console.log(`Started: ${new Date().toISOString()}\n`);

  // Launch all users simultaneously
  const startTime = Date.now();
  const promises = [];
  for (let i = 1; i <= CONCURRENT_USERS; i++) {
    promises.push(simulateLogin(i));
  }

  const results = await Promise.all(promises);
  const totalTime = Date.now() - startTime;

  // Analyze results
  const ok = results.filter(r => r.status === 'OK').length;
  const csrf419 = results.filter(r => r.status === 'CSRF_FAIL').length;
  const errors = results.filter(r => r.status === 'ERROR').length;
  const other = results.filter(r => !['OK','CSRF_FAIL','ERROR'].includes(r.status)).length;
  const times = results.map(r => r.time);
  const avgTime = Math.round(times.reduce((a,b) => a+b, 0) / times.length);
  const maxTime = Math.max(...times);
  const minTime = Math.min(...times);

  console.log('--- Results ---');
  console.log(`Total Users:     ${CONCURRENT_USERS}`);
  console.log(`Successful (302): ${ok}`);
  console.log(`419 CSRF Fail:   ${csrf419}`);
  console.log(`Errors:          ${errors}`);
  console.log(`Other:           ${other}`);
  console.log(`\n--- Timing ---`);
  console.log(`Total Time:      ${totalTime}ms`);
  console.log(`Avg per user:    ${avgTime}ms`);
  console.log(`Min:             ${minTime}ms`);
  console.log(`Max:             ${maxTime}ms`);
  console.log(`Success Rate:    ${((ok / CONCURRENT_USERS) * 100).toFixed(1)}%`);

  // Show any failures
  const failures = results.filter(r => r.status !== 'OK');
  if (failures.length > 0) {
    console.log(`\n--- Failures ---`);
    failures.forEach(f => console.log(`  User ${f.userId}: ${f.status} ${f.error || ''} (${f.time}ms)`));
  }

  console.log(`\n=== Test Complete ===\n`);
}

runTest().catch(console.error);
