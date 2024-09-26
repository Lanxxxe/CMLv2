function clearPickedColors2() {
  const ordersContainer = document.querySelector('.colors.picked-container');
  const len = ordersContainer.children.length;
  for (let i = len-1; i >= 0; --i) {
    const pickedColor = ordersContainer.children[i];
    const colorSpan = pickedColor.querySelector('.picked-color-code');
    const deleteColor = pickedColor.querySelector('.delete-color-btn>.material-symbols-outlined');
    deleteColor.removeEventListener('click', deleteColor.__deletePickedColor);
    colorSpan.removeEventListener('click', colorSpan.__setColor);
    pickedColor.remove();
  }
}


function displayPickedColors2(visualizer, pickedColors) {
  if (!pickedColors || Object.keys(pickedColors).length <= 0 || typeof(pickedColors) !== 'object') {
    window.location.href = "color-change.php?step=2";
    return;
  }
  const materialSymbol = "material-symbols-outlined";
  clearPickedColors2();

  function setCurrentColor(color, container) {
    visualizer.setColor(color);
    for (const elem of container.children) {
      const colorSpan = elem.querySelector('.picked-color-code');
      colorSpan.classList.remove(materialSymbol);
      colorSpan.textContent = '';
    }
    this.classList.add(materialSymbol);
    this.textContent = 'check';
  }

  function deletePickedColor(colorName) {
    delete pickedColors[colorName];
    localStorage.setItem('pickedColors', JSON.stringify(pickedColors));
    displayPickedColors2(visualizer, pickedColors);
    const orderCount = Object.values(pickedColors).length;
    updateOrderCount(orderCount);
  }

  const ordersContainer = document.querySelector('.colors.picked-container');

  Object.entries(pickedColors).forEach(([colorName, colorCode], i) => {
    const colorDiv = document.createElement('div');
    colorDiv.classList.add('saved-colors');

    const typeColorDiv = document.createElement('div');
    typeColorDiv.classList.add('type-color');

    const colorSpan = document.createElement('span');
    colorSpan.style.backgroundColor = colorCode;
    colorSpan.classList.add('picked-color-code');

    const colorDetailsDiv = document.createElement('div');
    colorDetailsDiv.classList.add('color-info-container');
    const colorNameSpan = document.createElement('span');
    colorNameSpan.textContent = colorName;

    const colorCodeSpan = document.createElement('span');
    colorCodeSpan.textContent = colorCode;

    const deleteButton = document.createElement('div');
    deleteButton.classList.add('delete-color-btn');
    const deleteButtonIcon = document.createElement('span');
    deleteButtonIcon.classList.add(materialSymbol);
    deleteButtonIcon.textContent = 'delete';

    colorSpan.__setColor = setCurrentColor.bind(colorSpan, colorCode, ordersContainer);
    colorSpan.addEventListener('click', colorSpan.__setColor);

    if (i === 0) {
      colorSpan.__setColor();
    }

    deleteButtonIcon.__deletePickedColor = deletePickedColor.bind(deleteButtonIcon, colorName);
    deleteButtonIcon.addEventListener('click', deleteButtonIcon.__deletePickedColor);

    // Append elements to their respective parent elements
    colorDetailsDiv.appendChild(colorNameSpan);
    colorDetailsDiv.appendChild(colorCodeSpan);
    deleteButton.appendChild(deleteButtonIcon);

    typeColorDiv.appendChild(colorSpan);
    typeColorDiv.appendChild(colorDetailsDiv);
    typeColorDiv.appendChild(deleteButton);
    colorDiv.appendChild(typeColorDiv);

    // Append the color div to the ordersContainer
    ordersContainer.appendChild(colorDiv);
  });
}

function updateOrderCount(pickedColorsLen) {
  const orderCountElem = document.querySelector('#order-count');
  orderCountElem.textContent = pickedColorsLen;
}


document.addEventListener('DOMContentLoaded', function () {
  if (!document.querySelector('.step3-container')) {
    return;
  }
  try {
    // The current color picked it is used in the line 200 if you make any changes to it go to line 200
    const visualizer = createVisualizer();
    let room = localStorage.getItem('room');

    if (!room || !visualizer.imageKeys.includes(room)) {
      window.location.href = "color-change.php?step=1";
    }

    const pickedColors = JSON.parse(localStorage.getItem('pickedColors')) || {};
    let pickedColorsLen = 0;
    if (!pickedColors || typeof(pickedColors) !== 'object') {
      window.location.href = "color-change.php?step=2";
      return;
    }

    pickedColorsLen = Object.keys(pickedColors).length;
    if (pickedColorsLen <= 0) {
      window.location.href = "color-change.php?step=2";
      return;
    }

    updateOrderCount(pickedColorsLen);

    const resetButton = document.querySelector('.reset-button');
    const undoButton = document.querySelector('.undo-button');
    const redoButton = document.querySelector('.redo-button');
    const orderPaint = document.querySelector('.order-paint');
    const savedMoreColor = document.querySelector('.add-color-container');
    const saveProjectButton = document.querySelector('.save-project');

    displayPickedColors2(visualizer, pickedColors);

    visualizer.setImage(room)
      .then(() => {
        const roomData = JSON.parse(localStorage.getItem('editedRoom'));
        visualizer.fillImage(roomData.objects);
      })
      .catch((err) => {
        console.log(err);
      });
    
    visualizer.canvas.addEventListener('mousemove', visualizer.hoverEffect);
    visualizer.canvas.addEventListener('mouseout', visualizer.hoverOff);
    visualizer.canvas.addEventListener('click', visualizer.fillObject);

    resetButton.addEventListener('click', visualizer.reset);
    undoButton.addEventListener('click', visualizer.undo);
    redoButton.addEventListener('click', visualizer.redo);
    orderPaint.addEventListener('click', visualizer.order);
    savedMoreColor.addEventListener('click', () => {window.location.href="color-change.php?step=2"});
    saveProjectButton.addEventListener('click', saveProject);

  } catch (err) {
    console.log(err);
  }
});