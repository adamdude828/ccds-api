#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const authDir = path.join(__dirname, '../.auth');
const authFile = path.join(authDir, 'user.json');

if (fs.existsSync(authFile)) {
  fs.unlinkSync(authFile);
  console.log('✅ Authentication state cleared');
  console.log('   Run tests to re-authenticate');
} else {
  console.log('ℹ️  No authentication state to clear');
}