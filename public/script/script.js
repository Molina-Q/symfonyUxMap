// Function to fetch POI data
async function fetchPOIData() {
    try {
        const response = await fetch("http://127.0.0.1:8000/map/js");
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log("Data fetched successfully");
        return data;
    } catch (error) {
        console.error("Error fetching POI data:", error);
        return null;
    }
}
const trajStart = document.getElementById("start");
const trajEnd = document.getElementById("end");

// Initialize map
var map = L.map("map").setView([48.8566, 2.3522], 7);

// Add tile layer
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

// Function to add markers to the map
function addMarkersToMap(poiData) {
    if (poiData && poiData.length > 0) {
        poiData.forEach((poi) => {
            L.marker([poi.latitude, poi.longitude])
                .addTo(map)
                .bindPopup(`<b>${poi.nom}</b><br>${poi.adresse}, ${poi.cp} ${poi.ville}`);
        });
        console.log(`Added ${poiData.length} markers to the map`);
    } else {
        console.log("No POI data to add to the map");
    }
}

let routingControl = null;

// Function to add routing
// Function to add or update routing
function addOrUpdateRouting() {
    const trajStart = localStorage.getItem("trajStart");
    const trajEnd = localStorage.getItem("trajEnd");

    if (trajStart && trajEnd) {
        const start = trajStart.split(',').map(Number);
        const end = trajEnd.split(',').map(Number);

        if (routingControl) {
            // Update existing route
            routingControl.setWaypoints([
                L.latLng(start[0], start[1]),
                L.latLng(end[0], end[1])
            ]);
        } else {
            // Create new route
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(start[0], start[1]),
                    L.latLng(end[0], end[1])
                ],
                routeWhileDragging: true
            }).addTo(map);
        }
        console.log("Routing added/updated on map");
    } else {
        // Remove existing route if localStorage is empty
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
            console.log("Routing removed from map");
        } else {
            console.log("No routing data available in localStorage");
        }
    }
}

function hydrateTrajectories(poiData) {  
    if (poiData && poiData.length > 0) {

        poiData.forEach((poi) => {
            const startOption = document.createElement("option");
            startOption.innerText = poi.nom;
            startOption.value = poi.latitude + ", " + poi.longitude;
            trajStart.appendChild(startOption);

            const endOption = document.createElement("option");
            endOption.innerText = poi.nom;
            endOption.value = poi.latitude + ", " + poi.longitude;
            trajEnd.appendChild(endOption);
        });

        // Add event listeners for selection changes
        trajStart.addEventListener('change', function() {
            localStorage.setItem("trajStart", this.value);
            addOrUpdateRouting();
        });

        trajEnd.addEventListener('change', function() {
            localStorage.setItem("trajEnd", this.value);
            addOrUpdateRouting();
        });
    }
}

// Main function to fetch data, add markers and routing
async function initializeMapWithPOIAndRouting() {
    const poiData = await fetchPOIData();
    if (poiData) {
        addMarkersToMap(poiData);
        hydrateTrajectories(poiData);
    } else {
        console.log("Failed to fetch POI data");
    }
    addOrUpdateRouting();
}

// Call the main function when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', initializeMapWithPOIAndRouting);