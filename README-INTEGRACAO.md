# 🔗 Integração Scraping → WordPress API

## 📋 Visão Geral

Este projeto fornece uma integração completa entre uma aplicação de scraping (Node.js) e a API WordPress para automatizar a criação de produtos com funcionalidades avançadas.

## 🚀 Instalação Rápida

### 1. Instalar Dependências

```bash
npm install
```

### 2. Configurar Credenciais

Edite o arquivo `config.js` ou defina as variáveis de ambiente:

```bash
export WP_USER_EMAIL="seu@email.com"
export WP_USER_PASSWORD="sua_senha"
export WP_API_URL="http://localhost:8000/wp-json/api/v1"
```

### 3. Executar Testes

```bash
npm test
```

### 4. Executar Exemplo

```bash
npm start
```

## 📁 Estrutura do Projeto

```
├── exemplo-integracao-scraping.js  # Classe principal de integração
├── test-integracao.js             # Testes automatizados
├── config.js                      # Configurações
├── package.json                   # Dependências NPM
├── INTEGRACAO-SCRAPING-API.md     # Documentação completa
└── README-INTEGRACAO.md          # Este arquivo
```

## 🔧 Uso Básico

### Importar e Configurar

```javascript
const IntegracaoAPIWordPress = require('./exemplo-integracao-scraping');

const api = new IntegracaoAPIWordPress({
  email: 'seu@email.com',
  password: 'sua_senha',
  baseURL: 'http://localhost:8000/wp-json/api/v1'
});
```

### Processar Produto do Scraping

```javascript
const dadosScraping = {
  nome: "Smartphone XYZ",
  referencia: "SM-XYZ-001",
  descricao: "Descrição do produto",
  preco: 899.99,
  imagens: ["./imagens/produto.jpg"],
  cores: [
    {
      nome: "Azul",
      tipo: "codigo",
      codigo: "#0000FF"
    }
  ],
  categorias: ["Eletrônicos", "Smartphones"]
};

const resultado = await api.processarProdutoScraping(dadosScraping);
console.log('Produto criado:', resultado);
```

## 🎨 Funcionalidades Suportadas

### ✅ Campos Obrigatórios
- `nome` - Nome do produto
- `referencia` - Referência única
- `descricao` - Descrição detalhada
- `imagens` - Array de imagens (mínimo 1)
- `cores` - Array de cores (mínimo 1)
- `categorias` - Array de categorias (mínimo 1)

### 🎨 Cores Híbridas
- **Tipo Código**: Hex, RGB, HSL
- **Tipo Imagem**: Upload de imagem da cor

### 🖼️ Upload de Imagens
- Upload automático para WordPress
- Suporte a múltiplas imagens
- Validação de formato e tamanho

### 📦 Categorias Múltiplas
- Array de categorias
- Validação de duplicatas
- Sanitização automática

## 🧪 Testes

### Executar Todos os Testes

```bash
npm test
```

### Testes Incluídos
- ✅ Autenticação JWT
- ✅ Validação de campos obrigatórios
- ✅ Upload de imagens
- ✅ Criação de produto simples
- ✅ Criação de produto complexo
- ✅ Tratamento de erros

## 📊 Monitoramento

### Logs Automáticos
- Timestamp de cada operação
- Status de sucesso/falha
- Detalhes de erros
- Métricas de performance

### Exemplo de Log
```
[2025-09-03T20:15:30.123Z] 🔑 Fazendo login...
[2025-09-03T20:15:30.456Z] ✅ Login realizado com sucesso
[2025-09-03T20:15:30.789Z] 📤 Fazendo upload da imagem: produto.jpg
[2025-09-03T20:15:31.123Z] ✅ Imagem enviada com sucesso
[2025-09-03T20:15:31.456Z] 📦 Criando produto: Smartphone XYZ
[2025-09-03T20:15:32.789Z] ✅ Produto criado com sucesso!
```

## 🚨 Tratamento de Erros

### Códigos de Erro Comuns

| Código | Descrição | Solução |
|--------|-----------|---------|
| 400 | Campo obrigatório | Verificar payload |
| 401 | Token inválido | Refazer login |
| 409 | Referência duplicada | Usar referência única |
| 413 | Arquivo muito grande | Reduzir tamanho da imagem |
| 500 | Erro interno | Verificar logs do servidor |

### Exemplo de Tratamento

```javascript
try {
  const resultado = await api.processarProdutoScraping(dados);
} catch (error) {
  if (error.response?.status === 409) {
    console.log('Referência duplicada, tentando com timestamp...');
    dados.referencia += `-${Date.now()}`;
    // Tentar novamente
  } else if (error.response?.status === 401) {
    console.log('Token expirado, fazendo novo login...');
    await api.login();
    // Tentar novamente
  }
}
```

## ⚙️ Configurações Avançadas

### Rate Limiting
```javascript
// Limitar a 10 requisições por minuto
const rateLimit = {
  maxRequests: 10,
  timeWindow: 60000
};
```

### Timeout Personalizado
```javascript
const timeouts = {
  request: 30000,  // 30 segundos
  upload: 60000,   // 60 segundos para uploads
  retry: 3         // 3 tentativas
};
```

### Processamento em Lote
```javascript
const produtos = [/* array de produtos */];
const resultados = await api.processarMultiplosProdutos(produtos, 2000);
```

## 📚 Documentação Completa

- **Documentação da API**: http://localhost:8000/api-docs/
- **Endpoint JSON**: http://localhost:8000/wp-json/api/v1/documentacao
- **Guia Detalhado**: `INTEGRACAO-SCRAPING-API.md`

## 🔗 Links Úteis

- [Swagger UI](http://localhost:8000/api-docs/) - Interface interativa
- [Documentação OpenAPI](http://localhost:8000/wp-json/api/v1/documentacao) - Especificação JSON
- [WordPress REST API](https://developer.wordpress.org/rest-api/) - Documentação oficial

## 🆘 Suporte

### Problemas Comuns

**Q: Erro 401 - Token inválido**
A: Verifique as credenciais e refaça o login

**Q: Erro 400 - Campo obrigatório**
A: Verifique se todos os campos obrigatórios estão preenchidos

**Q: Erro 409 - Referência duplicada**
A: Use uma referência única para cada produto

**Q: Upload de imagem falha**
A: Verifique o tamanho e formato da imagem (máx 5MB, JPG/PNG/WebP)

### Contato
- 📧 Email: suporte@exemplo.com
- 📚 Documentação: http://localhost:8000/api-docs/
- 🐛 Issues: [GitHub Issues](https://github.com/seu-repo/issues)

## 📄 Licença

MIT License - veja o arquivo LICENSE para detalhes.

---

**🎯 Objetivo**: Integrar perfeitamente o scraping com a API WordPress para automatizar a criação de produtos com todas as funcionalidades avançadas implementadas.
