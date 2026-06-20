<?php
// translation_header.php
// Este ficheiro deve ser incluído no <head> de todas as páginas.

$userLang = $_SESSION["language"] ?? 'pt';
?>
<script src="theme_handler.js"></script>
<!-- Link para o Manifesto PWA -->
<link rel="manifest" href="manifest.json">
<!-- Google Translate Logic -->
<script>
    (function() {
        var lang = "<?php echo $userLang; ?>";
        if (lang && lang !== 'pt') {
            var langPath = '/pt/' + lang;
            document.cookie = "googtrans=" + langPath + "; path=/";
            document.cookie = "googtrans=" + langPath + "; domain=" + document.domain + "; path=/";
        } else if (lang === 'pt') {
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=" + document.domain + "; path=/;";
        }
    })();

    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'pt',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }

    async function changeLanguage(langCode, btn) {
        console.log("A mudar idioma para:", langCode);
        
        // MOSTRAR LOADER APENAS QUANDO NECESSÁRIO (MUDANÇA DE IDIOMA)
        if (window.showGlobalLoader) window.showGlobalLoader();

        // 1. Definir cookie IMEDIATAMENTE
        var langPath = '/pt/' + langCode;
        document.cookie = "googtrans=" + langPath + "; path=/; SameSite=Lax";
        document.cookie = "googtrans=" + langPath + "; domain=" + document.domain + "; path=/; SameSite=Lax";

        // 2. Atualizar UI visual
        if (btn) {
            document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        // 3. Tentar gravar na BD para persistência futura
        try {
            const response = await fetch("api_update_profile.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ language: langCode })
            });
            
            // 4. Se estiver no mapa, avisar o script.js
            if (window.updateAudioLanguage) {
                window.updateAudioLanguage(langCode);
            }
        } catch (err) {
            console.error("Erro ao gravar na BD:", err);
        }

        // 5. Recarregar após um pequeno delay para garantir que os cookies "assentam"
        setTimeout(() => {
            location.reload();
        }, 300);
    }
</script>

<style>
    /* Hide Google Translate original UI components globally */
    #google_translate_element {
        opacity: 0;
        position: absolute;
        z-index: -1;
        width: 0;
        height: 0;
        overflow: hidden;
    }
    .goog-te-banner-frame.skiptranslate, 
    .goog-te-gadget-icon,
    .goog-te-banner,
    iframe.skiptranslate,
    #goog-gt-tt,
    .goog-te-balloon-frame {
        display: none !important;
        visibility: hidden !important;
    }
    body {
        top: 0px !important;
    }
    .goog-te-gadget-simple {
        background-color: transparent !important;
        border: none !important;
    }
</style>
