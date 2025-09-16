const fs = require('fs');
const path = require('path');

// Маппинг размеров иконок
const sizeMapping = {
  14: 'xs',
  16: 'sm', 
  20: 'md',
  24: 'md',
  28: 'lg',
  32: 'lg',
  40: 'xl',
  48: 'xl',
  50: 'xl',
  64: 'xl',
  80: 'xl'
};

// Функция для обновления файла
function updateFile(filePath) {
  try {
    let content = fs.readFileSync(filePath, 'utf8');
    let modified = false;

    // Заменяем числовые размеры на строковые
    content = content.replace(/size=\{(\d+)\}/g, (match, size) => {
      const mappedSize = sizeMapping[size];
      if (mappedSize) {
        modified = true;
        return `size="${mappedSize}"`;
      }
      return match;
    });

    if (modified) {
      fs.writeFileSync(filePath, content);
      console.log(`Updated: ${filePath}`);
      return true;
    }
    return false;
  } catch (error) {
    console.error(`Error updating ${filePath}:`, error.message);
    return false;
  }
}

// Функция для рекурсивного поиска файлов
function findTsxFiles(dir) {
  const files = [];
  
  function traverse(currentDir) {
    const items = fs.readdirSync(currentDir);
    
    for (const item of items) {
      const fullPath = path.join(currentDir, item);
      const stat = fs.statSync(fullPath);
      
      if (stat.isDirectory() && !item.startsWith('.') && item !== 'node_modules') {
        traverse(fullPath);
      } else if (item.endsWith('.tsx') || item.endsWith('.ts')) {
        files.push(fullPath);
      }
    }
  }
  
  traverse(dir);
  return files;
}

// Основная функция
function main() {
  const srcDir = path.join(__dirname, '..', 'src');
  const files = findTsxFiles(srcDir);
  
  console.log(`Found ${files.length} TypeScript files`);
  
  let updatedCount = 0;
  
  for (const file of files) {
    if (updateFile(file)) {
      updatedCount++;
    }
  }
  
  console.log(`Updated ${updatedCount} files`);
}

main();
