// 附近討論功能 JavaScript

let map;
let userMarker;
let postMarkers = [];
let userLocation = null;
let currentTileLayer = null; // 當前的地圖圖層
let currentLocationCircle = null; // 當前位置圓圈
let isUsingCurrentLocation = true; // 是否使用目前位置
let geoapifyApiKey = '909bbe471da94f1a8eee1bd450c5c4bf'; // Geoapify API Key

// Cookie 工具函數
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// 初始化附近討論頁面
function initNearbyDiscussion(location, posts) {
    userLocation = location;
    document.getElementById('locationPrompt').style.display = 'none';
    document.getElementById('contentWrapper').style.display = 'block';
    
    initMap(location);
    displayPosts(posts);
    updateMapMarkers(posts);
}

// 初始化地圖（使用 Geoapify + Leaflet）
function initMap(location) {
    // 如果地圖已存在，先移除
    if (map) {
        map.remove();
        map = null;
    }
    
    // 確保地圖容器存在
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('找不到地圖容器');
        return;
    }
    
    // 建立地圖
    map = L.map('map').setView([location.lat, location.lng], 13);
    
    // 取得地圖樣式（從 Cookie 或預設值）
    const savedStyle = getCookie('map_style') || 'osm-bright';
    
    // 設定樣式選擇器的值
    const styleSelect = document.getElementById('mapStyleSelect');
    if (styleSelect) {
        styleSelect.value = savedStyle;
    }
    
    // 加入 Geoapify 圖層
    addMapTileLayer(savedStyle);

    // 標記用戶位置（使用藍色圓形圖標）
    const userIcon = L.divIcon({
        className: 'user-location-marker',
        html: '<div style="width: 16px; height: 16px; background: #4285F4; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
    
    userMarker = L.marker([location.lat, location.lng], {
        icon: userIcon,
        title: '您的位置'
    }).addTo(map);

    // 添加圓圈顯示搜尋範圍
    const radiusSelect = document.getElementById('radiusSelect');
    const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
    
    // 清除舊圓圈
    if (currentLocationCircle) {
        map.removeLayer(currentLocationCircle);
    }
    
    currentLocationCircle = L.circle([location.lat, location.lng], {
        radius: radius * 1000, // 轉換為公尺
        color: '#4285F4',
        fillColor: '#4285F4',
        fillOpacity: 0.1,
        weight: 2,
        opacity: 0.3
    }).addTo(map);
    
    // 觸發地圖重新調整大小，確保正確顯示
    setTimeout(function() {
        map.invalidateSize();
    }, 100);
}

// 添加地圖圖層
function addMapTileLayer(mapStyle) {
    // 如果已有圖層，先移除
    if (currentTileLayer) {
        map.removeLayer(currentTileLayer);
    }
    
    currentTileLayer = L.tileLayer(`https://maps.geoapify.com/v1/tile/${mapStyle}/{z}/{x}/{y}.png?apiKey=${geoapifyApiKey}`, {
        attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a>',
        maxZoom: 19
    });
    
    currentTileLayer.addTo(map);
}

// 更新位置顯示文字
function updateLocationDisplay(lat, lng, locationName = null) {
    const displayText = document.getElementById('locationDisplayText');
    if (displayText) {
        if (locationName) {
            displayText.textContent = locationName;
        } else {
            displayText.textContent = `緯度: ${lat.toFixed(6)}, 經度: ${lng.toFixed(6)}`;
        }
    }
}

// 地點搜尋功能
function searchLocation(query) {
    if (!query || query.trim() === '') {
        return;
    }
    
    const searchBtn = document.getElementById('locationSearchBtn');
    const searchResults = document.getElementById('locationSearchResults');
    
    searchBtn.disabled = true;
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 搜尋中...';
    searchResults.innerHTML = '';
    searchResults.classList.remove('active');
    
    // 使用 Geoapify Geocoding API
    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&lang=zh&limit=5&apiKey=${geoapifyApiKey}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i> 搜尋';
            
            if (data.features && data.features.length > 0) {
                searchResults.innerHTML = '';
                data.features.forEach(feature => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    
                    const name = feature.properties.name || feature.properties.formatted;
                    const address = feature.properties.formatted || '';
                    
                    resultItem.innerHTML = `
                        <div class="result-name">${escapeHtml(name)}</div>
                        <div class="result-address">${escapeHtml(address)}</div>
                    `;
                    
                    resultItem.addEventListener('click', function() {
                        const lat = feature.geometry.coordinates[1];
                        const lng = feature.geometry.coordinates[0];
                        
                        // 更新位置
                        userLocation = { lat, lng };
                        isUsingCurrentLocation = false;
                        
                        // 更新地圖
                        initMap(userLocation);
                        
                        // 載入該位置的附近貼文
                        const radiusSelect = document.getElementById('radiusSelect');
                        const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
                        fetchNearbyPosts(lat, lng, radius);
                        
                        // 更新顯示
                        updateLocationDisplay(lat, lng, name);
                        
                        // 隱藏搜尋結果
                        searchResults.classList.remove('active');
                        document.getElementById('locationSearchInput').value = name;
                    });
                    
                    searchResults.appendChild(resultItem);
                });
                searchResults.classList.add('active');
            } else {
                searchResults.innerHTML = '<div class="search-result-item" style="color: #999; text-align: center; cursor: default;">找不到相關地點</div>';
                searchResults.classList.add('active');
            }
        })
        .catch(error => {
            console.error('搜尋錯誤:', error);
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i> 搜尋';
            alert('搜尋失敗，請稍後再試');
        });
}

// 顯示貼文列表
function displayPosts(posts) {
    const container = document.getElementById('postsContainer');
    const countElement = document.getElementById('postsCount');
    
    countElement.textContent = `附近討論 (${posts.length})`;
    
    if (posts.length === 0) {
        container.innerHTML = `
            <div class="no-posts">
                <i class="fas fa-inbox"></i>
                <p>目前附近還沒有討論，快來發表第一篇吧！</p>
                ${document.querySelector('.new-post-btn') ? '<a href="nearby_post.php" class="new-post-link">發表貼文</a>' : ''}
            </div>
        `;
        // 即使沒有貼文，也要清除舊標記
        postMarkers.forEach(marker => map.removeLayer(marker));
        postMarkers = [];
        return;
    }
    
    container.innerHTML = posts.map(post => {
        const distance = post.distance ? `${post.distance} 公里` : '';
        const locationInfo = post.LocationName ? `<span class="post-location"><i class="fas fa-map-marker-alt"></i> ${post.LocationName}</span>` : '';
        const avatar = post.AvatarURL ? 
            `<img src="../${post.AvatarURL}" alt="頭像" class="user-avatar">` : 
            `<div class="default-avatar"><span>👤</span></div>`;
        
        return `
            <div class="post-card" data-post-id="${post.PostID}" data-lat="${post.Latitude}" data-lng="${post.Longitude}">
                <div class="post-header">
                    <div class="user-info">
                        ${avatar}
                        <span class="username">${escapeHtml(post.Username)}</span>
                        ${post.Status ? `<span class="user-status">${escapeHtml(post.Status)}</span>` : ''}
                    </div>
                    <span class="post-date">${formatDate(post.PostDate)}</span>
                </div>
                <h3 class="post-title">
                    <a href="nearby_discuss.php?post_id=${post.PostID}">${escapeHtml(post.Title)}</a>
                </h3>
                <div class="post-content">${escapeHtml(post.Content)}</div>
                <div class="post-footer">
                    ${locationInfo}
                    ${distance ? `<span class="post-distance"><i class="fas fa-ruler"></i> 距離 ${distance}</span>` : ''}
                    <a href="nearby_discuss.php?post_id=${post.PostID}" class="read-more-btn">閱讀更多</a>
                </div>
            </div>
        `;
    }).join('');
    
    // 添加點擊事件，點擊貼文時地圖聚焦
    container.querySelectorAll('.post-card').forEach(card => {
        card.addEventListener('click', function() {
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            map.setView([lat, lng], 15);
        });
    });
}

// 更新地圖標記
function updateMapMarkers(posts) {
    // 清除舊標記
    postMarkers.forEach(marker => map.removeLayer(marker));
    postMarkers = [];
    
    // 添加新標記
    posts.forEach(post => {
        const postIcon = L.divIcon({
            className: 'post-marker',
            html: '<div style="width: 12px; height: 12px; background: #4CAF50; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });
        
        const marker = L.marker([parseFloat(post.Latitude), parseFloat(post.Longitude)], {
            icon: postIcon,
            title: post.Title
        }).addTo(map);
        
        const popupContent = `
            <div style="padding: 5px; min-width: 200px;">
                <h4 style="margin: 0 0 5px 0; font-size: 14px;">${escapeHtml(post.Title)}</h4>
                <p style="margin: 0; color: #666; font-size: 12px;">${escapeHtml(post.Username)}</p>
                ${post.distance ? `<p style="margin: 5px 0 0 0; color: #999; font-size: 11px;">距離 ${post.distance} 公里</p>` : ''}
                <a href="nearby_discuss.php?post_id=${post.PostID}" style="display: inline-block; margin-top: 8px; color: #4CAF50; text-decoration: none; font-size: 12px;">查看詳情 →</a>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        
        postMarkers.push(marker);
    });
}

// 取得附近貼文
async function fetchNearbyPosts(lat, lng, radius) {
    try {
        const response = await fetch(`get_nearby_posts.php?lat=${lat}&lng=${lng}&radius=${radius}`);
        const data = await response.json();
        
        if (data.success) {
            displayPosts(data.posts);
            updateMapMarkers(data.posts);
        } else {
            console.error('取得貼文失敗:', data.error);
            // 顯示錯誤訊息
            const container = document.getElementById('postsContainer');
            if (container) {
                container.innerHTML = `
                    <div class="no-posts">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>載入貼文時發生錯誤：${data.error || '未知錯誤'}</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('請求失敗:', error);
        const container = document.getElementById('postsContainer');
        if (container) {
            container.innerHTML = `
                <div class="no-posts">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>無法載入貼文，請稍後再試</p>
                </div>
            `;
        }
    }
}

// 啟用位置服務
document.getElementById('enableLocationBtn')?.addEventListener('click', function() {
    if (navigator.geolocation) {
        this.textContent = '定位中...';
        this.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userLocation = { lat, lng };
                
                // 儲存位置到 cookie（30天有效）
                setCookie('user_lat', lat, 30);
                setCookie('user_lng', lng, 30);
                
                // 初始化地圖並載入貼文（不顯示在 URL）
                document.getElementById('locationPrompt').style.display = 'none';
                document.getElementById('contentWrapper').style.display = 'block';
                document.getElementById('locationSearchSection').style.display = 'block';
                
                // 延遲一點時間確保容器已顯示
                setTimeout(function() {
                    initMap(userLocation);
                    const radiusSelect = document.getElementById('radiusSelect');
                    const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
                    fetchNearbyPosts(lat, lng, radius);
                    updateLocationDisplay(lat, lng);
                }, 100);
            },
            function(error) {
                alert('無法取得位置：' + error.message);
                document.getElementById('enableLocationBtn').textContent = '允許位置存取';
                document.getElementById('enableLocationBtn').disabled = false;
            }
        );
    } else {
        alert('您的瀏覽器不支援地理定位功能');
    }
});

// 重新定位
document.getElementById('refreshLocationBtn')?.addEventListener('click', function() {
    if (navigator.geolocation) {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 定位中...';
        this.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userLocation = { lat, lng };
                isUsingCurrentLocation = true;
                
                // 更新 cookie
                setCookie('user_lat', lat, 30);
                setCookie('user_lng', lng, 30);
                
                // 切換到「目前位置」標籤
                document.getElementById('currentLocationTab').click();
                
                // 重新初始化地圖並載入貼文（不顯示在 URL）
                initMap(userLocation);
                const radiusSelect = document.getElementById('radiusSelect');
                const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
                fetchNearbyPosts(lat, lng, radius);
                
                // 更新顯示
                updateLocationDisplay(lat, lng);
                
                document.getElementById('refreshLocationBtn').innerHTML = '<i class="fas fa-sync-alt"></i> 重新定位';
                document.getElementById('refreshLocationBtn').disabled = false;
            },
            function(error) {
                alert('無法取得位置：' + error.message);
                document.getElementById('refreshLocationBtn').innerHTML = '<i class="fas fa-sync-alt"></i> 重新定位';
                document.getElementById('refreshLocationBtn').disabled = false;
            }
        );
    }
});

// 標籤切換功能
document.getElementById('currentLocationTab')?.addEventListener('click', function() {
    // 切換標籤樣式
    this.classList.add('active');
    document.getElementById('searchLocationTab').classList.remove('active');
    
    // 切換內容面板
    document.getElementById('currentLocationContent').classList.add('active');
    document.getElementById('searchLocationContent').classList.remove('active');
    
    // 如果使用目前位置，重新載入貼文
    if (isUsingCurrentLocation && userLocation) {
        const radiusSelect = document.getElementById('radiusSelect');
        const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
        fetchNearbyPosts(userLocation.lat, userLocation.lng, radius);
        updateLocationDisplay(userLocation.lat, userLocation.lng);
    }
});

document.getElementById('searchLocationTab')?.addEventListener('click', function() {
    // 切換標籤樣式
    this.classList.add('active');
    document.getElementById('currentLocationTab').classList.remove('active');
    
    // 切換內容面板
    document.getElementById('currentLocationContent').classList.remove('active');
    document.getElementById('searchLocationContent').classList.add('active');
});

// 地點搜尋按鈕
document.getElementById('locationSearchBtn')?.addEventListener('click', function() {
    const query = document.getElementById('locationSearchInput').value.trim();
    searchLocation(query);
});

// 按 Enter 鍵搜尋
document.getElementById('locationSearchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = this.value.trim();
        searchLocation(query);
    }
});

// 搜尋範圍變更
document.getElementById('radiusSelect')?.addEventListener('change', function() {
    if (userLocation) {
        const radius = parseFloat(this.value);
        
        // 重新載入貼文
        fetchNearbyPosts(userLocation.lat, userLocation.lng, radius);
        
        // 重新繪製地圖圓圈
        if (map && userLocation) {
            // 清除舊圓圈
            if (currentLocationCircle) {
                map.removeLayer(currentLocationCircle);
            }
            
            // 添加新圓圈
            currentLocationCircle = L.circle([userLocation.lat, userLocation.lng], {
                radius: radius * 1000,
                color: '#4285F4',
                fillColor: '#4285F4',
                fillOpacity: 0.1,
                weight: 2,
                opacity: 0.3
            }).addTo(map);
        }
    }
});

// 地圖樣式變更
document.getElementById('mapStyleSelect')?.addEventListener('change', function() {
    const selectedStyle = this.value;
    
    // 儲存到 Cookie（30天有效）
    setCookie('map_style', selectedStyle, 30);
    
    // 更新地圖樣式
    if (map) {
        addMapTileLayer(selectedStyle);
    }
});

// 工具函數
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return '剛剛';
    if (minutes < 60) return `${minutes} 分鐘前`;
    if (hours < 24) return `${hours} 小時前`;
    if (days < 7) return `${days} 天前`;
    
    return date.toLocaleDateString('zh-TW');
}

