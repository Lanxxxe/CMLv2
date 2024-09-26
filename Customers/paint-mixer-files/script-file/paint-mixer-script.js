// NOTE: This script contains functionality for Step 1 and Step 2 of the program
const bedRoomContainer = document.querySelector('#bedroom-container');
const livingRoomContainer = document.querySelector('#living-room-container');
const allPallets = document.querySelector('.all-pallet');
const curatedPallets = document.querySelector('.curated-pallet');
const searchColor = document.querySelector('.search-pallet');
const paintRoomButton = document.querySelector('.paint-the-room');
const searchPallets = document.querySelector('.search-pallet');
const curateName = document.querySelector('#curate-Name');


let listOfOrders = {};
let currentImage;

const getColors = (jsonURL) => {
    return fetch(jsonURL)
        .then(response => response.json())
        .then(allColors => {
            return allColors
        })
        .catch(error => {
            console.log(error)
        })
}



const createCircleColors = () => {
    getColors("./paint-mixer-files/script-file/colors.json")
        .then(response => {
            Object.values(response).forEach(allColors => {
                Object.entries(allColors).forEach(([baseColor, colorShades]) => {
                    const colorNavigation = document.querySelector("#circle-colors-container");
                    const colorName = document.querySelector('#color-name');
                    let colorCircles = document.createElement('span');
                    colorCircles.style.background = String(baseColor);
                    colorCircles.classList.add('activeBase');
                    colorCircles.setAttribute('id', String(baseColor));
                    colorNavigation.appendChild(colorCircles);

                    // Add event listener to each span
                    colorCircles.addEventListener('click', function () {
                        // Ensure that classList property is available
                        if (this.classList) {
                            // Remove any existing 'selected' class from other spans
                            const allSpans = document.querySelectorAll('.activeBase');
                            allSpans.forEach(span => {
                                span.classList.remove('selected')
                                span.classList.remove('activeSpan');
                            });

                            this.classList.add('selected');
                            this.classList.add('activeSpan')
                            colorName.innerHTML = this.id;
                            displayColorShades(this.id);
                        }
                    });
                });
            });
        }).catch(error => console.log(error));
}


const displayColorShades = (activeBaseColor) => {
    getColors('./paint-mixer-files/script-file/colors.json')
        .then(response => {
            const SquareContainer = document.querySelector('#square-colors-container');
            const NumOfOrders = document.querySelector('#order-count');
            // Clear existing color squares
            SquareContainer.innerHTML = '';

            // Loop through color shades of the active base color
            for (let shades in response.Colors[activeBaseColor]) {
                let colorSquares = document.createElement('span');
                let hexCode = response.Colors[activeBaseColor][shades];
                colorSquares.style.background = hexCode;
                colorSquares.setAttribute('id', hexCode);
                SquareContainer.appendChild(colorSquares);

                colorSquares.addEventListener('click', () => {
                    // Toggle the 'picked-color' class
                    colorSquares.classList.toggle('picked-color');
                    // If the color is picked, add it to the listOfOrders object
                    if (colorSquares.classList.contains('picked-color')) {
                        listOfOrders[shades] = hexCode;
                    } else {
                        // If the color is unpicked, remove it from the listOfOrders object
                        delete listOfOrders[shades];
                    }
                    NumOfOrders.innerHTML = Object.entries(listOfOrders).length;
                    displayPickedColors();
                });
            }
        });
};




const displayPickedColors = () => {
    const ordersContainer = document.querySelector('.picked-container');
    ordersContainer.innerHTML = '';

    // Iterate over each entry (colorName, colorCode) in the listOfOrders object
    Object.entries(listOfOrders).forEach(([colorName, colorCode]) => {

        // Create the HTML structure for each color entry
        const colorDiv = document.createElement('div');
        colorDiv.classList.add('saved-colors');

        const typeColorDiv = document.createElement('div');
        typeColorDiv.classList.add('type-color');
        const colorSpan = document.createElement('span');
        colorSpan.style.backgroundColor = colorCode;
        const colorDetailsDiv = document.createElement('div');
        const colorNameSpan = document.createElement('span');
        colorNameSpan.textContent = colorName;

        const colorCodeSpan = document.createElement('span');
        colorCodeSpan.textContent = colorCode;

        // Append elements to their respective parent elements
        typeColorDiv.appendChild(colorSpan);
        colorDetailsDiv.appendChild(colorNameSpan);
        colorDetailsDiv.appendChild(colorCodeSpan);
        typeColorDiv.appendChild(colorDetailsDiv);
        colorDiv.appendChild(typeColorDiv);

        // Append the color div to the ordersContainer
        ordersContainer.appendChild(colorDiv);
    });
    localStorage.setItem('pickedColors', JSON.stringify(listOfOrders));
}


const getCuratedColors = (spanContainers, curatedSet, curatedPosition, head) => {


    spanContainers.forEach((span, index) => {
        const NumOfOrders = document.querySelector('#order-count');
        span.style.background = curatedSet[curatedPosition][index];
        span.addEventListener('click', () => {
            // alert(`${head[curatedPosition]}, ${curatedSet[curatedPosition][index]}`);
            // Toggle the 'picked-color' class
            span.classList.toggle('picked-color');
            // If the color is picked, add it to the listOfOrders object
            if (span.classList.contains('picked-color')) {
                listOfOrders[`${head[curatedPosition]}${index}`] = curatedSet[curatedPosition][index];
            } else {
                // If the color is unpicked, remove it from the listOfOrders object
                delete listOfOrders[`${head[curatedPosition]}${index}`];
            }
            NumOfOrders.innerHTML = Object.entries(listOfOrders).length;
            displayPickedColors();
        });

    })
}

const displayCuratedShades = (spanContainers, curatedSet, curatedPosition) => {
    //  Loop through the span container
    
    spanContainers.forEach((span, index) => {
        span.style.background = curatedSet[curatedPosition][index];
        console.log(curatedSet[curatedPosition][index]);
        
    })
}

const displayCuratedColor = () => {
    getColors('./paint-mixer-files/script-file/curated-pallet.json')
        .then(response => {  
            
            var retreaterContainer = document.querySelector('.retreater-container');
            var weaverContainer = document.querySelector('.weaver-container');
            var commonerContainer = document.querySelector('.commoner-container');
            var braveContainer = document.querySelector('.brave-container');
            var curatedColors = Object.values(response);
            var curatedHead = Object.keys(response);
            
            // Generate colors on the span containers
            getCuratedColors(document.querySelectorAll('.retreater-pallet span'), curatedColors, 0, curatedHead);
            getCuratedColors(document.querySelectorAll('.weaver-pallet span'), curatedColors, 1, curatedHead);
            getCuratedColors(document.querySelectorAll('.commoner-pallet span'), curatedColors, 2, curatedHead);
            getCuratedColors(document.querySelectorAll('.brave-pallet span'), curatedColors, 3, curatedHead);  
            
            getCuratedColors(document.querySelectorAll('.retreater-spans'), curatedColors, 0, curatedHead);
            getCuratedColors(document.querySelectorAll('.weaver-spans'), curatedColors, 1, curatedHead);
            getCuratedColors(document.querySelectorAll('.commoner-spans'), curatedColors, 2, curatedHead);
            getCuratedColors(document.querySelectorAll('.brave-spans'), curatedColors, 3, curatedHead);
            
            try {
                retreaterContainer.addEventListener('click', () => {
                    try{
                        window.location.href = 'color-change.php?step=2c&curate=retreater';
                        
                    } catch (error){
                        alert(`Error: ${error}`);
                    }
                });
                weaverContainer.addEventListener('click', () => {
                    try{
                        window.location.href = 'color-change.php?step=2c&curate=weaver';
                        
                    } catch (error){
                        alert(`Error: ${error}`);
                    }
                });
                commonerContainer.addEventListener('click', () => {
                    try{
                        window.location.href = 'color-change.php?step=2c&curate=commoner';
                        
                    } catch (error){
                        alert(`Error: ${error}`);
                    }
                });
                braveContainer.addEventListener('click', () => {
                    try{
                        window.location.href = 'color-change.php?step=2c&curate=brave';
                        
                    } catch (error){
                        alert(`Error: ${error}`);
                    }
                });
            } catch (error) {
                console.log(`An error occur: ${error}`);
            }

        });   
}

const gotoStep2 = (link, roomType) => {
    window.location.href = link;
    let typeOfRoom = roomType;
    currentImage = roomType;
    localStorage.setItem('room', typeOfRoom);
}

const browseColorCollection = (link) => {
    window.location.href = link;
}

// Search function 
const search = () => {
    // Get the search query entered by the user
    var query = document.getElementById("searchInput").value.toLowerCase();
    var searchText = document.querySelector('#result-title');

    if (query.trim() === '') {
      // Clear search results if search query is empty
      clearResults();
      return;
    }

    // Fetch data from JSON file
    fetch('./paint-mixer-files/script-file/colors.json')
      .then(response => response.json())
      .then(data => {
        var matchingColors = [];

        // Iterate over each category
        Object.keys(data.Colors).forEach(category => {
            // Iterate over color names in each category
            Object.keys(data.Colors[category]).forEach(colorName => {
                // Get the hex code of the color
                const hexCode = data.Colors[category][colorName];
                // Check if the color name matches the search query
                if (colorName.toLowerCase().includes(query)) {
                    // Add the matching color name along with its hex code to the list
                    matchingColors.push({ colorName: colorName, hexCode: hexCode });
                }
            });
        });

        // Display search results
        var resultsContainer = document.getElementById("searchResults");
        resultsContainer.innerHTML = "";

        if (matchingColors.length > 0) {
            searchText.innerHTML = "Seach Result";
          // Iterate over each matching color
            matchingColors.forEach(color => {
                // Create a new div element for the color result
                const colorResultDiv = document.createElement('div');
                colorResultDiv.classList.add('colors-result-container');
                colorResultDiv.setAttribute('id', color.hexCode);

                // Create the inner HTML for the color result
                colorResultDiv.innerHTML = `
                    <div class="bg-here"></div>
                    <div>
                        <h4>${color.colorName}</h4>
                        <p>${color.hexCode}</p>
                    </div>
                `;

                // Set the background color of the bg-here div
                const bgHereDiv = colorResultDiv.querySelector('.bg-here');
                bgHereDiv.style.backgroundColor = color.hexCode;

                // Append the color result div to the results container
                resultsContainer.appendChild(colorResultDiv);
                try {
                    const NumOfOrders = document.querySelector('#order-count');
                    colorResultDiv.addEventListener('click', () => {
                        // alert(`${color.colorName}, ${color.hexCode}`);
                        colorResultDiv.classList.toggle('picked-color');
                        bgHereDiv.classList.toggle('picked-color');
    
                        if (colorResultDiv.classList.contains('picked-color')) {
                            listOfOrders[color.colorName] = color.hexCode
                        } else {
                            delete listOfOrders[color.colorName];
                        }
                        NumOfOrders.textContent = Object.entries(listOfOrders).length;
                        displayPickedColors();
                    })
                } catch(err) {
                    alert(err)
                }
            });
        } else {
            searchText.innerHTML = "No Color Found";
        }
      })
      .catch(error => console.error('Error fetching data:', error));
  }

  const clearResults = () => {
    // Clear search results
    document.getElementById("searchResults").innerHTML = "";
  }

  const focus = () => {
    document.querySelector('.search-bar').setAttribute('id', 'focus');
  }


document.addEventListener("DOMContentLoaded", () => {
    try {
        createCircleColors();
        displayCuratedColor();
    } catch (err) {
        console.log(err);
    }

    if (bedRoomContainer) {
        bedRoomContainer.addEventListener('click', () => {
            gotoStep2('color-change.php?step=2', 'bedroom');
        });
    }

    if (livingRoomContainer) {
        livingRoomContainer.addEventListener('click', () => {
            gotoStep2('color-change.php?step=2', 'living-room');
        });
    }

    if (allPallets) {
        allPallets.addEventListener('click', () => {
            window.location.href = 'color-change.php?step=2a';
        });
    }
    if (curatedPallets) {
        curatedPallets.addEventListener('click', () => {
            window.location.href = 'color-change.php?step=2b';

        });
    }
    if (paintRoomButton) {
        paintRoomButton.addEventListener('click', () => {
            window.location.href = 'color-change.php?step=3';
        })
    }
    if (searchColor) {
        searchColor.addEventListener('click', () => {
            window.location.href = 'color-change.php?step=2d'
        })
    }
})


