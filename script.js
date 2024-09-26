function search() {
    // Get the search query entered by the user
    var query = document.getElementById("searchInput").value.toLowerCase();

    if (query.trim() === '') {
      // Clear search results if search query is empty
      clearResults();
      return;
    }

    // Fetch data from JSON file
    fetch('colors.json') // Assuming your JSON file is named colors.json
      .then(response => response.json())
      .then(data => {
        // Filter color names based on search query
        var matchingColors = [];

        // Iterate over each category
        Object.keys(data.Colors).forEach(category => {
          // Iterate over color names in each category
          Object.keys(data.Colors[category]).forEach(colorName => {
            // Check if the color name matches the search query
            if (colorName.toLowerCase().includes(query)) {
              // Add the matching color name along with its category to the list
              matchingColors.push({ category: category, colorName: colorName });
            }
          });
        });

        // Display search results
        var resultsContainer = document.getElementById("searchResults");
        resultsContainer.innerHTML = "";

        if (matchingColors.length > 0) {
          // Create a div for each matching color name along with its category
          matchingColors.forEach(color => {
            var resultDiv = document.createElement("div");
            resultDiv.textContent = color.colorName + " (" + color.category + ")";
            resultsContainer.appendChild(resultDiv);
          });
        } else {
          // If no results found, display a message
          var noResultsMessage = document.createElement("p");
          noResultsMessage.textContent = "No results found.";
          resultsContainer.appendChild(noResultsMessage);
        }
      })
      .catch(error => console.error('Error fetching data:', error));
  }

  function clearResults() {
    // Clear search results
    document.getElementById("searchResults").innerHTML = "";
  }