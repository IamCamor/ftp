#!/usr/bin/env node

import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { StdioClientTransport } from '@modelcontextprotocol/sdk/client/stdio.js';
import { spawn } from 'child_process';

class MCPPlaywrightClient {
  constructor() {
    this.client = null;
    this.transport = null;
  }

  async connect() {
    try {
      // Запускаем MCP сервер
      const serverProcess = spawn('npx', ['puppeteer-mcp-server'], {
        stdio: ['pipe', 'pipe', 'inherit']
      });

      // Создаем транспорт
      this.transport = new StdioClientTransport({
        reader: serverProcess.stdout,
        writer: serverProcess.stdin
      });

      // Создаем клиент
      this.client = new Client(
        {
          name: 'fishtrackpro-mcp-client',
          version: '1.0.0'
        },
        {
          capabilities: {
            resources: {},
            tools: {}
          }
        }
      );

      // Подключаемся
      await this.client.connect(this.transport);
      console.log('✅ MCP Playwright client connected successfully!');

      // Получаем список доступных инструментов
      const tools = await this.client.listTools();
      console.log('🔧 Available tools:', tools.tools.map(t => t.name));

      return true;
    } catch (error) {
      console.error('❌ Failed to connect to MCP server:', error);
      return false;
    }
  }

  async navigateToUrl(url) {
    try {
      const result = await this.client.callTool({
        name: 'navigate',
        arguments: { url }
      });
      console.log('🌐 Navigated to:', url);
      return result;
    } catch (error) {
      console.error('❌ Navigation failed:', error);
      throw error;
    }
  }

  async takeScreenshot(filename = 'screenshot.png') {
    try {
      const result = await this.client.callTool({
        name: 'screenshot',
        arguments: { filename }
      });
      console.log('📸 Screenshot saved:', filename);
      return result;
    } catch (error) {
      console.error('❌ Screenshot failed:', error);
      throw error;
    }
  }

  async clickElement(selector) {
    try {
      const result = await this.client.callTool({
        name: 'click',
        arguments: { selector }
      });
      console.log('🖱️ Clicked element:', selector);
      return result;
    } catch (error) {
      console.error('❌ Click failed:', error);
      throw error;
    }
  }

  async typeText(selector, text) {
    try {
      const result = await this.client.callTool({
        name: 'type',
        arguments: { selector, text }
      });
      console.log('⌨️ Typed text:', text, 'into:', selector);
      return result;
    } catch (error) {
      console.error('❌ Type failed:', error);
      throw error;
    }
  }

  async getPageContent() {
    try {
      const result = await this.client.callTool({
        name: 'getContent',
        arguments: {}
      });
      console.log('📄 Page content retrieved');
      return result;
    } catch (error) {
      console.error('❌ Get content failed:', error);
      throw error;
    }
  }

  async close() {
    if (this.client) {
      await this.client.close();
      console.log('🔌 MCP client disconnected');
    }
  }
}

// Пример использования
async function main() {
  const mcpClient = new MCPPlaywrightClient();
  
  try {
    // Подключаемся к серверу
    const connected = await mcpClient.connect();
    if (!connected) {
      process.exit(1);
    }

    // Тестируем FishTrackPro
    console.log('\n🎣 Testing FishTrackPro with MCP Playwright...\n');
    
    // Переходим на главную страницу
    await mcpClient.navigateToUrl('http://localhost:5173');
    await new Promise(resolve => setTimeout(resolve, 2000)); // Ждем загрузки
    
    // Делаем скриншот
    await mcpClient.takeScreenshot('fishtrackpro-home.png');
    
    // Получаем содержимое страницы
    const content = await mcpClient.getPageContent();
    console.log('📄 Page title:', content.content?.[0]?.text?.substring(0, 100) + '...');
    
    // Пытаемся кликнуть на кнопку "Добавить улов" (если есть)
    try {
      await mcpClient.clickElement('button[data-testid="add-catch"]');
      await new Promise(resolve => setTimeout(resolve, 1000));
      await mcpClient.takeScreenshot('fishtrackpro-add-catch.png');
    } catch (error) {
      console.log('ℹ️ Add catch button not found or not clickable');
    }
    
    // Переходим на карту
    try {
      await mcpClient.clickElement('button[data-testid="map-nav"]');
      await new Promise(resolve => setTimeout(resolve, 2000));
      await mcpClient.takeScreenshot('fishtrackpro-map.png');
    } catch (error) {
      console.log('ℹ️ Map navigation not found');
    }

    console.log('\n✅ MCP Playwright test completed successfully!');
    
  } catch (error) {
    console.error('❌ Test failed:', error);
  } finally {
    await mcpClient.close();
  }
}

// Запускаем если файл выполняется напрямую
if (import.meta.url === `file://${process.argv[1]}`) {
  main().catch(console.error);
}

export default MCPPlaywrightClient;
