#!/usr/bin/env node

import { spawn } from 'child_process';

async function testMCP() {
  console.log('🚀 Starting MCP Playwright test...');

  // Проверяем доступность пакетов
  try {
    const { Client } = await import('@modelcontextprotocol/sdk/client/index.js');
    console.log('✅ MCP SDK available');
  } catch (error) {
    console.log('❌ MCP SDK not available:', error.message);
  }

  try {
    // Проверяем бинарный файл
    const { execSync } = await import('child_process');
    execSync('which mcp-server-puppeteer', { stdio: 'ignore' });
    console.log('✅ Puppeteer MCP server binary available');
  } catch (error) {
    console.log('❌ Puppeteer MCP server binary not available');
  }

  // Простой тест без MCP
  console.log('\n🧪 Running basic test...');

  console.log('📦 Available npm packages:');
  const packages = [
    '@modelcontextprotocol/sdk',
    'puppeteer-mcp-server',
    '@hisma/server-puppeteer'
  ];

  for (const pkg of packages) {
    try {
      if (pkg === 'puppeteer-mcp-server') {
        // Проверяем бинарный файл
        const { execSync } = await import('child_process');
        execSync('which mcp-server-puppeteer', { stdio: 'ignore' });
        console.log(`✅ ${pkg} - installed (binary available)`);
      } else {
        await import(pkg);
        console.log(`✅ ${pkg} - installed`);
      }
    } catch (error) {
      console.log(`❌ ${pkg} - not found`);
    }
  }

  console.log('\n🎯 MCP Playwright setup completed!');
  console.log('📖 See MCP_SETUP.md for usage instructions');
}

testMCP().catch(console.error);
