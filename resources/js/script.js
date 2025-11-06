/* JS central del proyecto */
document.addEventListener('DOMContentLoaded', () => {
    // Ejemplo: instalar PWA si se desea
    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      const btn = document.querySelector('#btnInstall');
      if(btn) btn.classList.remove('d-none');
    });
    const install = document.querySelector('#btnInstall');
    if(install){
      install.addEventListener('click', async () => {
        if(!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        install.classList.add('d-none');
      });
    }
  });

