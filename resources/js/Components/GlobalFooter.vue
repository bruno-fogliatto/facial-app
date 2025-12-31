<script setup>
</script>

<template>
  <footer class="global-footer">
    <div class="footer-content">
      <!-- <div class="footer-links">
        <a href="https://www.bluefoxmarina.com.br/contato/" class="footer-link">Contato</a>
        <span class="separator">|</span>
        <a href="https://www.facebook.com/bluefoxmarina/" class="footer-link">Termos de Uso</a>
      </div> -->
      <div class="copyright">
        © 2025 Marina Blue Fox. Todos os direitos reservados.
      </div>
    </div>
  </footer>
</template>

<style scoped>
  .global-footer {
    background: white;
    border-top: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
    margin-top: auto;
  }

  .footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .footer-links {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .footer-link {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
  }

  .footer-link:hover {
    color: #007bff;
  }

  .separator {
    color: #dee2e6;
  }

  .copyright {
    color: #6c757d;
    font-size: 0.9rem;
  }

  @media (max-width: 768px) {
    .global-footer {
      padding: 1rem;
    }
    
    .footer-content {
      flex-direction: column;
      gap: 1rem;
      text-align: center;
    }
  }
</style>
