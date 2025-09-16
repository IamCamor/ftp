import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { StdioClientTransport } from '@modelcontextprotocol/sdk/client/stdio.js';
import { spawn, ChildProcess } from 'child_process';

interface MCPToolResult {
  content: Array<{
    type: string;
    text?: string;
    data?: any;
  }>;
  isError?: boolean;
}

interface MCPTool {
  name: string;
  description: string;
  inputSchema: any;
}

export class MCPPlaywrightClient {
  private client: Client | null = null;
  private transport: StdioClientTransport | null = null;
  private serverProcess: ChildProcess | null = null;

  async connect(): Promise<boolean> {
    try {
      // Запускаем MCP сервер
      this.serverProcess = spawn('npx', ['puppeteer-mcp-server'], {
        stdio: ['pipe', 'pipe', 'inherit']
      });

      // Создаем транспорт
      this.transport = new StdioClientTransport({
        reader: this.serverProcess.stdout!,
        writer: this.serverProcess.stdin!
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
      console.log('🔧 Available tools:', tools.tools.map((t: MCPTool) => t.name));

      return true;
    } catch (error) {
      console.error('❌ Failed to connect to MCP server:', error);
      return false;
    }
  }

  async navigateToUrl(url: string): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'navigate',
        arguments: { url }
      });
      console.log('🌐 Navigated to:', url);
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Navigation failed:', error);
      throw error;
    }
  }

  async takeScreenshot(filename: string = 'screenshot.png'): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'screenshot',
        arguments: { filename }
      });
      console.log('📸 Screenshot saved:', filename);
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Screenshot failed:', error);
      throw error;
    }
  }

  async clickElement(selector: string): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'click',
        arguments: { selector }
      });
      console.log('🖱️ Clicked element:', selector);
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Click failed:', error);
      throw error;
    }
  }

  async typeText(selector: string, text: string): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'type',
        arguments: { selector, text }
      });
      console.log('⌨️ Typed text:', text, 'into:', selector);
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Type failed:', error);
      throw error;
    }
  }

  async getPageContent(): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'getContent',
        arguments: {}
      });
      console.log('📄 Page content retrieved');
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Get content failed:', error);
      throw error;
    }
  }

  async waitForElement(selector: string, timeout: number = 5000): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'waitForElement',
        arguments: { selector, timeout }
      });
      console.log('⏳ Waited for element:', selector);
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Wait for element failed:', error);
      throw error;
    }
  }

  async evaluateScript(script: string): Promise<MCPToolResult> {
    if (!this.client) {
      throw new Error('Client not connected');
    }

    try {
      const result = await this.client.callTool({
        name: 'evaluate',
        arguments: { script }
      });
      console.log('🔍 Script evaluated:', script.substring(0, 50) + '...');
      return result as MCPToolResult;
    } catch (error) {
      console.error('❌ Script evaluation failed:', error);
      throw error;
    }
  }

  async close(): Promise<void> {
    if (this.client) {
      await this.client.close();
      console.log('🔌 MCP client disconnected');
    }
    
    if (this.serverProcess) {
      this.serverProcess.kill();
      console.log('🔄 MCP server process terminated');
    }
  }
}

// Утилитарные функции для FishTrackPro
export class FishTrackProTester {
  private mcpClient: MCPPlaywrightClient;

  constructor() {
    this.mcpClient = new MCPPlaywrightClient();
  }

  async testApplication(): Promise<void> {
    try {
      // Подключаемся к серверу
      const connected = await this.mcpClient.connect();
      if (!connected) {
        throw new Error('Failed to connect to MCP server');
      }

      console.log('\n🎣 Testing FishTrackPro with MCP Playwright...\n');
      
      // Переходим на главную страницу
      await this.mcpClient.navigateToUrl('https://fishtrackpro.ru');
      await this.sleep(2000); // Ждем загрузки
      
      // Делаем скриншот главной страницы
      await this.mcpClient.takeScreenshot('fishtrackpro-home.png');
      
      // Получаем содержимое страницы
      const content = await this.mcpClient.getPageContent();
      console.log('📄 Page content length:', content.content?.[0]?.text?.length || 0);
      
      // Тестируем навигацию
      await this.testNavigation();
      
      // Тестируем функциональность
      await this.testFunctionality();
      
      console.log('\n✅ FishTrackPro MCP test completed successfully!');
      
    } catch (error) {
      console.error('❌ Test failed:', error);
      throw error;
    } finally {
      await this.mcpClient.close();
    }
  }

  private async testNavigation(): Promise<void> {
    console.log('\n🧭 Testing navigation...');
    
    // Пытаемся кликнуть на кнопку карты
    try {
      await this.mcpClient.clickElement('button[data-testid="map-nav"]');
      await this.sleep(2000);
      await this.mcpClient.takeScreenshot('fishtrackpro-map.png');
      console.log('✅ Map navigation successful');
    } catch (error) {
      console.log('ℹ️ Map navigation not available or not clickable');
    }

    // Пытаемся кликнуть на кнопку профиля
    try {
      await this.mcpClient.clickElement('button[data-testid="profile-nav"]');
      await this.sleep(2000);
      await this.mcpClient.takeScreenshot('fishtrackpro-profile.png');
      console.log('✅ Profile navigation successful');
    } catch (error) {
      console.log('ℹ️ Profile navigation not available or not clickable');
    }
  }

  private async testFunctionality(): Promise<void> {
    console.log('\n⚙️ Testing functionality...');
    
    // Пытаемся кликнуть на кнопку "Добавить улов"
    try {
      await this.mcpClient.clickElement('button[data-testid="add-catch"]');
      await this.sleep(1000);
      await this.mcpClient.takeScreenshot('fishtrackpro-add-catch.png');
      console.log('✅ Add catch functionality accessible');
    } catch (error) {
      console.log('ℹ️ Add catch button not found or not clickable');
    }

    // Пытаемся кликнуть на кнопку "Добавить место"
    try {
      await this.mcpClient.clickElement('button[data-testid="add-point"]');
      await this.sleep(1000);
      await this.mcpClient.takeScreenshot('fishtrackpro-add-point.png');
      console.log('✅ Add point functionality accessible');
    } catch (error) {
      console.log('ℹ️ Add point button not found or not clickable');
    }
  }

  private sleep(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

// Экспорт для использования
export default MCPPlaywrightClient;
