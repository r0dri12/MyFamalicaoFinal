// Coordenadas centrais de Famalicão
const famalicaoCoords = [41.407, -8.519];

// Inicializar o mapa
const map = L.map('map', {
    zoomControl: false // Vamos adicionar o zoom control noutra posição
}).setView(famalicaoCoords, 14);

// Adicionar a camada de mapa (estilo claro moderno do CartoDB)
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '<span class="notranslate">&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a></span>',
    subdomains: 'abcd',
    maxZoom: 20
}).addTo(map);

// Forçar o mapa a desenhar-se corretamente
setTimeout(() => {
    map.invalidateSize();
    console.log("Mapa reinicializado para garantir visibilidade.");
}, 500);

// Reposicionar o controlo de zoom para não sobrepor a sidebar
L.control.zoom({
    position: 'bottomright'
}).addTo(map);

// Array para guardar os pontos da rota selecionados
let currentRoute = [];
let pendingPoiId = null;
let isFindingStart = false;

// Linha da rota no mapa (Polyline) - Agora gerida pelo Routing Machine
let routingControl = L.Routing.control({
    waypoints: [],
    routeWhileDragging: false,
    addWaypoints: false,
    draggableWaypoints: false,
    autoRoute: true, // Garante que a rota é calculada automaticamente
    fitSelectedRoutes: false,
    showAlternatives: false,
    lineOptions: {
        styles: [{ color: '#3b82f6', opacity: 0.6, weight: 6, dashArray: '10, 10' }]
    },
    createMarker: function () { return null; }, // Não queremos os marcadores padrão do routing
    show: false // Esconder o painel de instruções padrão
}).addTo(map);

// Ouvir quando a rota é encontrada para calcular distância e tempo
routingControl.on('routesfound', function (e) {
    console.log("Rota encontrada:", e.routes);
    const routes = e.routes;
    const summary = routes[0].summary;

    const distanceKm = (summary.totalDistance / 1000).toFixed(1);
    const timeMin = Math.round(summary.totalTime / 60);

    const summaryDiv = document.getElementById('route-summary');
    const distSpan = document.getElementById('route-distance');
    const timeSpan = document.getElementById('route-time');

    if (summaryDiv && distSpan && timeSpan) {
        summaryDiv.style.display = 'block';
        distSpan.innerHTML = `<i class="ph-bold ph-map-trifold"></i> ${distanceKm} km`;
        timeSpan.innerHTML = `<i class="ph-bold ph-clock"></i> ${timeMin} min`;
    }
});

// Lógica de Bottom Sheet deslizante para Mobile
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const handle = document.querySelector('.mobile-handle');
    const header = document.querySelector('.sidebar-header');
    const btnReopen = document.getElementById('btn-reopen-routes');

    if (!sidebar) return;

    let isDragging = false;
    let autoHideTimer = null;

    function setSidebarHeight(height) {
        sidebar.style.height = `${height}px`;
    }

    window.sideSnap = function (state) {
        // Limpar timer se houver interação manual
        if (autoHideTimer) {
            clearTimeout(autoHideTimer);
            autoHideTimer = null;
        }

        // state: 'expanded' ou 'hidden'
        sidebar.classList.remove('dragging', 'expanded', 'hidden');
        sidebar.style.height = '';

        if (state === 'expanded') {
            sidebar.classList.add('expanded');
            if (btnReopen) btnReopen.classList.remove('visible');
            setTimeout(() => map.invalidateSize(), 400);
        } else if (state === 'hidden') {
            sidebar.classList.add('hidden');
            if (btnReopen) btnReopen.classList.add('visible');
            setTimeout(() => map.invalidateSize(), 400);
        }
    }

    // Eventos de toque para arrastar
    const onTouchStart = (e) => {
        if (window.innerWidth > 768) return;

        if (autoHideTimer) {
            clearTimeout(autoHideTimer);
            autoHideTimer = null;
        }

        startY = e.touches[0].clientY;
        startHeight = sidebar.offsetHeight;
        isDragging = true;
        sidebar.classList.add('dragging');
        sidebar.classList.remove('hidden'); // Tirar do hidden se começar a arrastar
    };

    const onTouchMove = (e) => {
        if (!isDragging) return;
        const currentY = e.touches[0].clientY;
        const deltaY = startY - currentY;
        let newHeight = startHeight + deltaY;

        // Limites de segurança
        if (newHeight < 0) newHeight = 0;
        if (newHeight > window.innerHeight * 0.9) newHeight = window.innerHeight * 0.9;

        setSidebarHeight(newHeight);
    };

    const onTouchEnd = (e) => {
        if (!isDragging) return;
        isDragging = false;
        const currentHeight = sidebar.offsetHeight;

        // Decidir snap binário (Expandido ou Escondido)
        // Se estiver acima de 25% da tela, expande. Senão, esconde.
        if (currentHeight > window.innerHeight * 0.25) {
            window.sideSnap('expanded');
        } else {
            window.sideSnap('hidden');
        }
    };

    // Aplicar a handle e header apenas em mobile
    [handle, header].forEach(el => {
        if (el) {
            el.addEventListener('touchstart', onTouchStart, { passive: true });
            el.addEventListener('touchmove', onTouchMove, { passive: true });
            el.addEventListener('touchend', onTouchEnd);
        }
    });

    // Botão de reabrir
    if (btnReopen) {
        btnReopen.addEventListener('click', () => {
            window.sideSnap('expanded');
        });
    }
});

// Dados fictícios de Pontos de Interesse (POIs) baseado no teu projeto
const pois = [
    {
        id: 1,
        name: "Parque da Devesa",
        coords: [41.4042, -8.5147],
        description: "Um dos maiores parques urbanos do país, ideal para caminhadas e lazer.",
        image: "https://www.parquedadevesa.com/op/image/?co=1113&h=ccd6c",
        type: "Natureza"
    },
    {
        id: 2,
        name: "Museu Bernardino Machado",
        coords: [41.4085, -8.5205],
        description: "Espaço dedicado à vida e obra do antigo Presidente da República.",
        image: "https://www.famalicao.pt/op/image/?co=183709&h=8dc50",
        type: "Cultura"
    },
    {
        id: 3,
        name: "Fundação Cupertino de Miranda",
        coords: [41.4055, -8.5190],
        description: "Centro de Estudo do Surrealismo e Torre com características únicas.",
        image: "https://rpac.pt/_rpac/wp-content/uploads/2023/09/Fundacao-Cupertino-Miranda-Exterior-2-FCM_2_ext.jpg",
        type: "Arte"
    },
    {
        id: 4,
        name: "Igreja Matriz Nova",
        coords: [41.4068, -8.5175],
        description: "Monumento religioso central com arquitetura imponente.",
        image: "https://images.mnstatic.com/83/ff/83ff5471cac7e0b42b062e0a16024e7c.jpg",
        type: "Monumento"
    }
];

// Ícone personalizado para os marcadores
const customIcon = L.divIcon({
    className: 'custom-map-marker',
    html: `<div style="background-color: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-size: 18px;"><i class="ph-fill ph-map-pin"></i></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

// Adicionar os marcadores ao mapa
pois.forEach(poi => {
    addMarkerToMap(poi, customIcon);
});

// Criar ícone para locais do utilizador
const userIcon = L.divIcon({
    className: 'custom-map-marker user-poi-marker',
    html: `<div style="background-color: var(--danger, #ef4444); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-size: 18px;"><i class="ph-fill ph-star"></i></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

// Carregar marcadores customizados do utilizador (evitando cache do browser)
fetch('api_custom_pois.php?t=' + new Date().getTime())
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.pois) {
            data.pois.forEach(poi => {
                pois.push(poi); // Adicionar à lista global
                addMarkerToMap(poi, userIcon); // Adicionar ao mapa
            });
        }
    })
    .catch(error => console.error('Erro ao carregar POIs:', error));

// Função genérica para adicionar marcadores
function addMarkerToMap(poi, iconType) {
    const marker = L.marker(poi.coords, { icon: iconType }).addTo(map);

    // Conteúdo do Popup estilo moderno
    const popupContent = `
        <div class="custom-popup">
            <img src="${poi.image}" alt="${poi.name}">
            <h3>${poi.name}</h3>
            <p>${poi.description}</p>
            <div style="display:flex; gap:8px; margin-top:12px;">
                <button class="add-poi-btn" onclick="addToRoute('${poi.id}')" style="flex:1;">
                    <i class="ph-bold ph-plus"></i> Adicionar
                </button>
                <button class="btn-audio-mini" onclick="speakPOI('${poi.id}')" title="Ouvir descrição" style="width:40px; height:40px; background:#eff6ff; color:var(--primary); border:none; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">
                    <i class="ph-fill ph-speaker-high"></i>
                </button>
            </div>
        </div>
    `;

    marker.bindPopup(popupContent, {
        closeButton: true,
        minWidth: 200
    });
}

// Clicar no Mapa para Adicionar Novo Local
let tempMarker = null;

map.on('click', function (e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    // Remover marcador temporário anterior se existir
    if (tempMarker) map.removeLayer(tempMarker);

    // Criar marcador temporário
    tempMarker = L.marker([lat, lng]).addTo(map);

    const formHtml = `
        <div class="custom-popup create-poi-form" style="text-align:left;">
            <h3 style="margin-bottom:12px; color:var(--primary);">Criar Meu Local</h3>
            <input type="text" id="new-poi-name" placeholder="Nome do Local (Ex: Restaurante X)" style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #ddd; border-radius:6px; font-family:Inter;">
            <textarea id="new-poi-desc" placeholder="Pequena descrição ou notas..." style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #ddd; border-radius:6px; font-family:Inter; resize:vertical; min-height:60px;"></textarea>
            
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; font-size:13px; cursor:pointer;">
                <input type="checkbox" id="new-poi-public" style="width:16px; height:16px;">
                Tornar público para a comunidade
            </label>

            <div style="display:flex; gap:8px;">
                <button onclick="saveCustomPoi(${lat}, ${lng})" style="flex:1; background:var(--primary); color:white; border:none; padding:8px; border-radius:6px; cursor:pointer; font-weight:600;">Guardar</button>
                <button onclick="map.closePopup()" style="background:#f1f5f9; color:#333; border:none; padding:8px; border-radius:6px; cursor:pointer;">Cancelar</button>
            </div>
        </div>
    `;

    tempMarker.bindPopup(formHtml, { closeButton: false }).openPopup();

    // Se o popup for fechado (clicando fora ou no cancelar), removemos o marcador
    tempMarker.on('popupclose', function () {
        if (tempMarker) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }
    });
});

// Função para Guardar Ponto Personalizado na BD
window.saveCustomPoi = function (lat, lng) {
    const nameInput = document.getElementById('new-poi-name').value;
    const descInput = document.getElementById('new-poi-desc').value;
    const isPublic = document.getElementById('new-poi-public').checked ? 1 : 0;

    if (!nameInput.trim()) {
        myFama.alert("Campo Obrigatório", "Por favor, dá um nome ao teu local.", "warning");
        return;
    }

    const payload = {
        name: nameInput,
        description: descInput || "Local adicionado por mim",
        lat: lat,
        lng: lng,
        is_public: isPublic
    };

    fetch('api_custom_pois.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const newPoi = data.poi;
                // A API agora devolve o type e image corretos
                newPoi.coords = [newPoi.lat, newPoi.lng];

                pois.push(newPoi);
                map.removeLayer(tempMarker); // Remove o formulário temporário
                addMarkerToMap(newPoi, userIcon); // Adiciona o marcador vermelho real
                myFama.toast("Local guardado com sucesso!", "success");
            } else {
                myFama.alert("Erro ao Guardar", data.message, "error");
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            myFama.alert("Erro de Autenticação", "Ocorreu um erro ao guardar o local. Estás autenticado?", "error");
        });
};

// Referências a elementos do DOM
const routeList = document.getElementById('route-list');
const routeCount = document.getElementById('route-count');
const btnExportGoogle = document.getElementById('btn-export-google');
const btnClear = document.getElementById('btn-clear');

// Função para adicionar um ponto à rota
window.addToRoute = function (id) {
    if (currentRoute.length === 0) {
        window.openStartModal(id);
        return;
    }

    // Garantir que a comparação é feita com strings
    const poi = pois.find(p => String(p.id) === String(id));

    if (!poi) {
        console.error("Local não encontrado:", id);
        return;
    }

    // Verificar se já existe na rota
    if (currentRoute.find(p => String(p.id) === String(id))) {
        myFama.toast("Este local já está na tua rota!", "info");
        return;
    }

    currentRoute.push(poi);
    updateRouteUI();
    map.closePopup();

    // Feedback Mobile: Auto-expandir sidebar e depois auto-hide
    if (window.innerWidth <= 768) {
        if (typeof window.sideSnap === 'function') {
            window.sideSnap('expanded');

            // Scroll suave para o fim da lista
            setTimeout(() => {
                const routeList = document.getElementById('route-list');
                if (routeList) routeList.scrollTop = routeList.scrollHeight;
            }, 400);

            // AUTO-HIDE após 3 segundos
            // Definimos um timer global para podermos cancelar se o user interagir
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                // Removemos timers antigos se existirem
                // (Já tratado dentro do window.sideSnap e onTouchStart, mas reforçamos aqui)

                // Agendar o fecho automático
                autoHideTimer = setTimeout(() => {
                    // Só esconde se ainda estiver expandido e se não houve interação manual intermédia
                    if (sidebar.classList.contains('expanded')) {
                        window.sideSnap('hidden');
                    }
                }, 1500);
            }
        }
    }
};

// Função para remover um ponto da rota
window.removeFromRoute = function (index) {
    currentRoute.splice(index, 1);
    updateRouteUI();
};

// Limpar toda a rota
btnClear.addEventListener('click', async () => {
    const confirmed = await myFama.confirm(
        "Limpar Roteiro",
        "Queres mesmo limpar a tua rota toda?",
        { isDanger: true, confirmText: "Limpar Tudo" }
    );

    if (confirmed) {
        currentRoute = [];
        updateRouteUI();
        myFama.toast("Roteiro limpo.", "info");
    }
});

// Atualizar interface visual da lista de rotas
function updateRouteUI() {
    const total = currentRoute.length;
    routeCount.textContent = `${total} locais`;

    if (total === 0) {
        routeList.innerHTML = `
            <li class="empty-state">
                <i class="ph-fill ph-map-trifold"></i>
                <p>Nenhum local selecionado.<br>Clica nos pontos do mapa para criar o teu roteiro.</p>
            </li>
        `;
        btnExportGoogle.disabled = true;
        btnClear.disabled = true;
        const btnSave = document.getElementById('btn-save-route');
        if (btnSave) btnSave.disabled = true;
        return;
    }

    btnExportGoogle.disabled = false;
    btnClear.disabled = false;
    const btnSave = document.getElementById('btn-save-route');
    if (btnSave) btnSave.disabled = false;

    routeList.innerHTML = '';

    currentRoute.forEach((poi, index) => {
        const li = document.createElement('li');
        li.className = 'route-item';

        // Atribuir ícones dinamicamente com base no tipo
        let iconClass = "ph-map-pin";
        if (poi.type === "Natureza") iconClass = "ph-tree";
        if (poi.type === "Cultura") iconClass = "ph-books";
        if (poi.type === "Arte") iconClass = "ph-palette";
        if (poi.type === "Monumento") iconClass = "ph-bank";
        if (poi.type === "Partida") iconClass = "ph-navigation-arrow";

        li.innerHTML = `
            <div class="route-item-icon" style="${poi.type === 'Partida' ? 'background:var(--primary); color:white;' : ''}">
                <i class="ph-fill ${iconClass}"></i>
            </div>
            <div class="route-item-info">
                <h3>${index + 1}. ${poi.name}</h3>
                <p>${poi.type}</p>
            </div>
            <div class="route-item-actions" style="display:flex; gap:4px;">
                <button class="audio-btn-small" onclick="speakPOI('${poi.id}')" title="Ouvir local" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:18px; padding:4px;">
                    <i class="ph-fill ph-speaker-high"></i>
                </button>
                <button class="remove-btn" onclick="removeFromRoute(${index})" title="Remover da rota">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
        `;
        routeList.appendChild(li);
    });

    // Atualizar o motor de rotas (Leaflet Routing Machine)
    if (total >= 2) {
        const waypoints = currentRoute.map(poi => L.latLng(poi.coords[0], poi.coords[1]));
        routingControl.setWaypoints(waypoints);
        routingControl.route(); // Forçar cálculo do novo percurso
    } else {
        routingControl.setWaypoints([]);
        const summaryDiv = document.getElementById('route-summary');
        if (summaryDiv) summaryDiv.style.display = 'none';
    }
}

// Exportar Rota para Google Maps (Usando Google Maps Directions URL)
btnExportGoogle.addEventListener('click', () => {
    if (currentRoute.length < 1) return;

    let url = "https://www.google.com/maps/dir/?api=1";

    // Formatar as coordenadas "latitude,longitude"
    const formatCoord = (coord) => `${coord[0]},${coord[1]}`;

    if (currentRoute.length === 1) {
        // Se for só 1 ponto, usa-o como destino
        url += `&destination=${formatCoord(currentRoute[0].coords)}`;
    }
    else {
        // Usa o 1º como origem, o último como destino, e os do meio como waypoints
        const origin = formatCoord(currentRoute[0].coords);
        const destination = formatCoord(currentRoute[currentRoute.length - 1].coords);

        url += `&origin=${origin}&destination=${destination}`;

        if (currentRoute.length > 2) {
            // Extrair os pontos intermédios (waypoints)
            const waypointsList = currentRoute.slice(1, currentRoute.length - 1);
            const waypointsStr = waypointsList.map(p => formatCoord(p.coords)).join('|');
            url += `&waypoints=${waypointsStr}`;
        }
    }

    // Abrir o link num novo separador (isto abria a app logo no telemovel)
    window.open(url, '_blank');
});

// Localização do Utilizador
let userLocationMarker = null;
let userLocationCircle = null;

window.locateUser = function () {
    if (!window.isSecureContext && window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
        myFama.alert("Ligação Insegura", "O iPhone (Safari) bloqueia a localização em sites que não usam 'https://'. Se estás a testar localmente, tenta aceder via HTTPS ou usa o PC.", "warning");
        return;
    }

    map.locate({
        setView: true,
        maxZoom: 16,
        enableHighAccuracy: true,
        timeout: 10000
    });
};

map.on('locationfound', function (e) {
    const radius = e.accuracy / 2;

    if (isFindingStart) {
        const gpsPoi = {
            id: 'gps_start_' + Date.now(),
            name: 'Minha Localização',
            coords: [e.latlng.lat, e.latlng.lng],
            type: 'Partida',
            image: 'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20'
        };

        currentRoute.push(gpsPoi);
        isFindingStart = false;

        if (pendingPoiId) {
            const poi = pois.find(p => String(p.id) === String(pendingPoiId));
            if (poi) currentRoute.push(poi);
            pendingPoiId = null;
        }

        updateRouteUI();
        myFama.toast("Partida definida pela tua localização!", "success");
    }

    if (!userLocationMarker) {
        // Criar ícone de ponto azul pulsante
        const blueDotIcon = L.divIcon({
            className: 'user-location-marker',
            html: `<div style="background-color: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);"></div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 11],
            popupAnchor: [0, -11]
        });

        userLocationMarker = L.marker(e.latlng, { icon: blueDotIcon }).addTo(map);
        userLocationMarker.bindPopup("Estás aqui!").openPopup();

        userLocationCircle = L.circle(e.latlng, {
            radius: radius,
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.15,
            weight: 1
        }).addTo(map);
    } else {
        userLocationMarker.setLatLng(e.latlng);
        userLocationCircle.setLatLng(e.latlng);
        userLocationCircle.setRadius(radius);
        userLocationMarker.openPopup();
    }
});

map.on('locationerror', function (e) {
    myFama.toast("Erro de Localização: " + e.message, "error");
    console.error("Location Error:", e);
});

// =========================================
// AUDIO GUIDE LOGIC (Web Speech API)
// =========================================

let speechUtterance = null;
let isPlayingRoute = false;
let currentAudioLang = 'pt-PT';
let preferredVoice = null;

// Função para buscar a melhor voz disponível automaticamente
function loadVoices() {
    const voices = window.speechSynthesis.getVoices();
    if (voices.length === 0) return;

    // Mapeamento de códigos de idioma para tags BCP 47
    const langMap = {
        'pt': 'pt-PT',
        'en': 'en-GB',
        'fr': 'fr-FR',
        'es': 'es-ES'
    };

    const targetLang = langMap[currentAudioLang.split('-')[0]] || currentAudioLang;

    // Prioridade para vozes de alta qualidade no idioma selecionado
    preferredVoice = voices.find(v => v.lang.startsWith(targetLang.split('-')[0]) && (v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('Helia'))) ||
        voices.find(v => v.lang.startsWith(targetLang.split('-')[0])) ||
        voices[0];

    if (preferredVoice) {
        console.log(`Áudio-Guia configurado para [${targetLang}] com a voz:`, preferredVoice.name);
    }
}

// Função chamada pelo map.php quando o idioma muda
window.updateAudioLanguage = function (langCode) {
    currentAudioLang = langCode;
    loadVoices(); // Recarregar a voz ideal para o novo idioma

    // Pequeno feedback sonoro no novo idioma (se não for PT para não chatear)
    if (langCode !== 'pt') {
        const welcome = {
            'en': 'Language changed to English',
            'fr': 'Langue changée en Français',
            'es': 'Idioma cambiado a Español'
        };
        const utter = new SpeechSynthesisUtterance(welcome[langCode]);
        utter.voice = preferredVoice;
        utter.lang = preferredVoice.lang;
        window.speechSynthesis.speak(utter);
    }
};

// Garantir que as vozes são carregadas (essencial para Chrome/Edge)
if (window.speechSynthesis.onvoiceschanged !== undefined) {
    window.speechSynthesis.onvoiceschanged = loadVoices;
}
setTimeout(loadVoices, 500);
loadVoices();

window.speakPOI = function (id) {
    const poi = pois.find(p => String(p.id) === String(id));
    if (!poi) return;

    // Se já estiver a falar, para
    window.stopAudio();

    const textToSpeak = `${poi.name}. ${poi.description}`;
    const utterance = new SpeechSynthesisUtterance(textToSpeak);

    if (preferredVoice) {
        utterance.voice = preferredVoice;
    }

    utterance.lang = preferredVoice ? preferredVoice.lang : (currentAudioLang === 'pt' ? 'pt-PT' : currentAudioLang);
    utterance.rate = 1.0; // Velocidade standard, ligeiramente ajustada pela voz natural
    utterance.pitch = 1.0;
    utterance.volume = 1.0;

    window.speechSynthesis.speak(utterance);
    speechUtterance = utterance;
};

window.playFullRouteAudio = function () {
    if (currentRoute.length === 0) {
        myFama.alert("Roteiro Vazio", "Adiciona locais à tua rota primeiro!", "info");
        return;
    }

    if (isPlayingRoute) {
        window.stopAudio();
        return;
    }

    isPlayingRoute = true;
    updateAudioButtonUI(true);

    let currentIndex = 0;

    function speakNext() {
        if (currentIndex >= currentRoute.length || !isPlayingRoute) {
            isPlayingRoute = false;
            updateAudioButtonUI(false);
            return;
        }

        const poi = currentRoute[currentIndex];
        const textToSpeak = `Ponto ${currentIndex + 1}: ${poi.name}. ${poi.description}`;
        const utterance = new SpeechSynthesisUtterance(textToSpeak);

        if (preferredVoice) {
            utterance.voice = preferredVoice;
        }

        utterance.lang = preferredVoice ? preferredVoice.lang : (currentAudioLang === 'pt' ? 'pt-PT' : currentAudioLang);
        utterance.rate = 1.0;

        utterance.onend = () => {
            currentIndex++;
            setTimeout(speakNext, 1200); // Pausa ligeiramente maior entre locais para naturalidade
        };

        window.speechSynthesis.speak(utterance);
        speechUtterance = utterance;
    }

    speakNext();
};

window.stopAudio = function () {
    window.speechSynthesis.cancel();
    isPlayingRoute = false;
    updateAudioButtonUI(false);
};

function updateAudioButtonUI(active) {
    const btnAudio = document.getElementById('btn-audio-main');
    if (!btnAudio) return;

    if (active) {
        btnAudio.innerHTML = '<i class="ph-fill ph-stop-circle"></i> Parar Áudio';
        btnAudio.style.backgroundColor = 'var(--danger)';
        btnAudio.style.color = 'white';
        btnAudio.classList.add('pulse-animation');
    } else {
        btnAudio.innerHTML = '<i class="ph-fill ph-speaker-high"></i> Áudio-Guia';
        btnAudio.style.backgroundColor = '#f1f5f9';
        btnAudio.style.color = 'var(--text-main)';
        btnAudio.classList.remove('pulse-animation');
    }
}

// =========================================
// ROUTE START LOGIC
// =========================================

window.openStartModal = function (poiId = null) {
    pendingPoiId = poiId;
    const modal = document.getElementById('startRouteModal');
    const selectedBtn = document.getElementById('btn-start-selected');

    if (poiId) {
        selectedBtn.style.display = 'block';
        const poi = pois.find(p => String(p.id) === String(poiId));
        if (poi) selectedBtn.innerHTML = `<i class="ph-bold ph-map-pin"></i> Começar em "${poi.name}"`;
    } else {
        selectedBtn.style.display = 'none';
    }

    modal.style.display = 'flex';
};

window.confirmStartRoute = function (method) {
    document.getElementById('startRouteModal').style.display = 'none';

    if (method === 'gps') {
        isFindingStart = true;
        myFama.toast("A obter a tua localização...", "info");
        window.locateUser();
    } else if (method === 'selected') {
        if (pendingPoiId) {
            const poi = pois.find(p => String(p.id) === String(pendingPoiId));
            if (poi) {
                currentRoute.push(poi);
                pendingPoiId = null;
                updateRouteUI();
                myFama.toast("Ponto de partida selecionado!", "success");
            }
        }
    }
};

// =========================================
// ROUTE HISTORY LOGIC
// =========================================

window.openSaveRouteModal = function () {
    document.getElementById('route-name-input').value = '';
    document.getElementById('saveRouteModal').style.display = 'flex';
};

window.confirmSaveRoute = function () {
    const routeName = document.getElementById('route-name-input').value.trim() || 'Meu Roteiro';
    const description = document.getElementById('route-desc-input').value.trim();
    const isPublic = document.getElementById('route-public-checkbox').checked ? 1 : 0;

    const payload = {
        name: routeName,
        description: description,
        is_public: isPublic,
        items: currentRoute.map(p => String(p.id))
    };

    fetch('api_routes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                myFama.toast(data.message, "success");
                document.getElementById('saveRouteModal').style.display = 'none';
            } else {
                myFama.alert("Erro ao Guardar", data.message, "error");
            }
        })
        .catch(err => console.error("Erro ao guardar rota:", err));
};

window.openHistoryModal = function () {
    document.getElementById('historyModal').style.display = 'flex';
    loadHistory();
};

function loadHistory() {
    const list = document.getElementById('history-list');
    list.innerHTML = '<p style="text-align:center; color:var(--text-muted);">A carregar histórico...</p>';

    fetch('api_routes.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.routes.length === 0) {
                    list.innerHTML = '<p style="text-align:center; color:var(--text-muted); padding:20px;">Ainda não guardaste nenhuma rota.</p>';
                    return;
                }

                list.innerHTML = data.routes.map(route => `
                <div style="background:#f8fafc; border:1px solid var(--border); border-radius:12px; padding:16px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h4 style="font-size:15px; font-weight:700; margin-bottom:4px;">${route.route_name}</h4>
                        <p style="font-size:12px; color:var(--text-muted);">${new Date(route.created_at).toLocaleDateString()}</p>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button onclick="loadRouteFromHistory(${route.id})" style="background:var(--primary); color:white; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600;">Carregar</button>
                        <button onclick="deleteRoute(${route.id})" style="background:#fee2e2; color:var(--danger); border:none; padding:8px; border-radius:8px; cursor:pointer;"><i class="ph-bold ph-trash"></i></button>
                    </div>
                </div>
            `).join('');
            }
        });
}

window.loadRouteFromHistory = function (id) {
    fetch(`api_routes.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const newRoute = [];
                data.items.forEach(item => {
                    let poi;
                    if (item.is_hardcoded) {
                        poi = pois.find(p => String(p.id) === String(item.id));
                    } else {
                        // It's a custom POI from potentially another user
                        poi = pois.find(p => String(p.id) === String(item.id));
                        if (!poi) {
                            poi = item;
                            pois.push(poi);
                            addMarkerToMap(poi, userIcon);
                        }
                    }
                    if (poi) newRoute.push(poi);
                });

                if (newRoute.length > 0) {
                    currentRoute = newRoute;
                    updateRouteUI();
                    document.getElementById('historyModal').style.display = 'none';
                    myFama.toast("Rota carregada com sucesso!", "success");

                    // Zoom to fit the route
                    const bounds = L.latLngBounds(newRoute.map(p => p.coords));
                    map.fitBounds(bounds, { padding: [50, 50] });
                } else {
                    myFama.alert("Erro", "Não foi possível carregar os locais desta rota.", "error");
                }
            }
        });
};

// Check for route in URL on startup
const urlParams = new URLSearchParams(window.location.search);
const loadRouteId = urlParams.get('load_route');
if (loadRouteId) {
    setTimeout(() => window.loadRouteFromHistory(loadRouteId), 1000);
}

window.deleteRoute = async function (id) {
    const confirmed = await myFama.confirm("Eliminar Rota", "Tens a certeza que queres eliminar este roteiro guardado?");
    if (confirmed) {
        fetch('api_routes.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    myFama.toast(data.message, "info");
                    loadHistory();
                }
            });
    }
};
