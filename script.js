// Coordenadas centrais de Famalicão
const famalicaoCoords = [41.407, -8.519];

// Inicializar o mapa
const map = L.map('map', {
    zoomControl: false // Vamos adicionar o zoom control noutra posição
}).setView(famalicaoCoords, 14);

// Adicionar a camada de mapa (estilo claro moderno do CartoDB)
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 20
}).addTo(map);

// Reposicionar o controlo de zoom para não sobrepor a sidebar
L.control.zoom({
    position: 'bottomright'
}).addTo(map);

// Array para guardar os pontos da rota selecionados
let currentRoute = [];

// Dados fictícios de Pontos de Interesse (POIs) baseado no teu projeto
const pois = [
    {
        id: 1,
        name: "Parque da Devesa",
        coords: [41.4042, -8.5147],
        description: "Um dos maiores parques urbanos do país, ideal para caminhadas e lazer.",
        image: "https://images.unsplash.com/photo-1587844053648-2895ea305260?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80", // Placeholder
        type: "Natureza"
    },
    {
        id: 2,
        name: "Museu Bernardino Machado",
        coords: [41.4085, -8.5205],
        description: "Espaço dedicado à vida e obra do antigo Presidente da República.",
        image: "https://images.unsplash.com/photo-1541123437800-1bb1317bc951?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        type: "Cultura"
    },
    {
        id: 3,
        name: "Fundação Cupertino de Miranda",
        coords: [41.4055, -8.5190],
        description: "Centro de Estudo do Surrealismo e Torre com características únicas.",
        image: "https://images.unsplash.com/photo-1574958269340-fa927503f3dd?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        type: "Arte"
    },
    {
        id: 4,
        name: "Igreja Matriz Nova",
        coords: [41.4068, -8.5175],
        description: "Monumento religioso central com arquitetura imponente.",
        image: "https://images.unsplash.com/photo-1548625361-ec8571ea7ab0?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
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

// Carregar marcadores customizados do utilizador
fetch('api_custom_pois.php')
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
            <button class="add-poi-btn" onclick="addToRoute('${poi.id}')">
                <i class="ph-bold ph-plus"></i> Adicionar à Rota
            </button>
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
            <div style="display:flex; gap:8px;">
                <button onclick="saveCustomPoi(${lat}, ${lng})" style="flex:1; background:var(--primary); color:white; border:none; padding:8px; border-radius:6px; cursor:pointer; font-weight:600;">Guardar</button>
                <button onclick="map.closePopup()" style="background:#f1f5f9; color:#333; border:none; padding:8px; border-radius:6px; cursor:pointer;">Cancelar</button>
            </div>
        </div>
    `;

    tempMarker.bindPopup(formHtml, { closeButton: false }).openPopup();
});

// Função para Guardar Ponto Personalizado na BD
window.saveCustomPoi = function (lat, lng) {
    const nameInput = document.getElementById('new-poi-name').value;
    const descInput = document.getElementById('new-poi-desc').value;

    if (!nameInput.trim()) {
        alert("Por favor, dá um nome ao teu local.");
        return;
    }

    const payload = {
        name: nameInput,
        description: descInput || "Local adicionado por mim",
        lat: lat,
        lng: lng
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
                newPoi.type = "Meu Local";
                newPoi.image = "https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"; // Imagem default de pin no mapa
                newPoi.coords = [newPoi.lat, newPoi.lng];

                pois.push(newPoi);
                map.removeLayer(tempMarker); // Remove o formulário temporário
                addMarkerToMap(newPoi, userIcon); // Adiciona o marcador vermelho real
                alert("Local guardado com sucesso!");
            } else {
                alert("Erro: " + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert("Ocorreu um erro ao guardar o local. Estás autenticado?");
        });
};

// Referências a elementos do DOM
const routeList = document.getElementById('route-list');
const routeCount = document.getElementById('route-count');
const btnExportGoogle = document.getElementById('btn-export-google');
const btnClear = document.getElementById('btn-clear');

// Função para adicionar um ponto à rota
window.addToRoute = function (id) {
    // Garantir que a comparação é feita com strings, pois os IDs custom são "custom_1" e os originais "1"
    const poi = pois.find(p => String(p.id) === String(id));

    if (!poi) {
        console.error("Local não encontrado:", id);
        return;
    }

    // Verificar se já existe na rota
    if (currentRoute.find(p => String(p.id) === String(id))) {
        alert("Este local já está na tua rota!");
        return;
    }

    currentRoute.push(poi);
    updateRouteUI();
    map.closePopup();
};

// Função para remover um ponto da rota
window.removeFromRoute = function (index) {
    currentRoute.splice(index, 1);
    updateRouteUI();
};

// Limpar toda a rota
btnClear.addEventListener('click', () => {
    if (confirm('Queres mesmo limpar a tua rota toda?')) {
        currentRoute = [];
        updateRouteUI();
    }
});

// Atualizar interface visual da lista de rotas
function updateRouteUI() {
    routeCount.textContent = `${currentRoute.length} locais`;

    if (currentRoute.length === 0) {
        routeList.innerHTML = `
            <li class="empty-state">
                <i class="ph-fill ph-map-trifold"></i>
                <p>Nenhum local selecionado.<br>Clica nos pontos do mapa para criar o teu roteiro.</p>
            </li>
        `;
        btnExportGoogle.disabled = true;
        btnClear.disabled = true;
        return;
    }

    btnExportGoogle.disabled = false;
    btnClear.disabled = false;

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

        li.innerHTML = `
            <div class="route-item-icon">
                <i class="ph-fill ${iconClass}"></i>
            </div>
            <div class="route-item-info">
                <h3>${index + 1}. ${poi.name}</h3>
                <p>${poi.type}</p>
            </div>
            <button class="remove-btn" onclick="removeFromRoute(${index})" title="Remover da rota">
                <i class="ph-bold ph-x"></i>
            </button>
        `;
        routeList.appendChild(li);
    });
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
