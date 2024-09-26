function isColorLight(color) {
    const r = parseInt(color.substring(1, 3), 16);
    const g = parseInt(color.substring(3, 5), 16);
    const b = parseInt(color.substring(5, 7), 16);

    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    return luminance > 0.5;
}


function displayToOrderColor(pickedColors) {
  const orderColorContainer = document.querySelector('.color-to-by');
  orderColorContainer.innerHTML = '';

  Object.entries(pickedColors).forEach(([colorName, colorCode]) => {
    const container = document.createElement('div');
    container.className = 'ordered-color';
    container.style.backgroundColor = colorCode;
    if (isColorLight(colorCode)) {
      container.style.color = "#333";
    } else {
      container.style.color = "#fff";
    }

    const colorNameElem = document.createElement('span');
    colorNameElem.className = 'color-name';
    colorNameElem.textContent = colorName;

    const colorCodeElem = document.createElement('span');
    colorCodeElem.className = 'color-code';
    colorCodeElem.textContent = colorCode;

    container.appendChild(colorNameElem);
    container.appendChild(colorCodeElem);
    orderColorContainer.appendChild(container);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  if (!document.querySelector('.step4-container')) {
    return;
  }

  const roomData = JSON.parse(localStorage.getItem('editedRoom'));
  const pickedColors = JSON.parse(localStorage.getItem('pickedColors')) || {};
  const pickedColorsLen = Object.keys(pickedColors).length;
  if (!pickedColors || typeof (pickedColors) !== 'object' || pickedColorsLen <= 0) {
    window.location.href = "color-change.php?step=2";
    return;
  }

  displayToOrderColor(pickedColors);

  const visualizer = createVisualizer();
  let room = localStorage.getItem('room');

  if (!room || !visualizer.imageKeys.includes(room)) {
    window.location.href = "color-change.php?step=1";
  }

  visualizer.setImage(room)
    .then(() => {
      visualizer.fillImage(roomData.objects);
    })
    .catch((err) => {
      console.log(err);
    });

  const saveProjectButton = document.querySelector('.save-project');
  saveProjectButton.addEventListener('click', saveProject);
  const tryAgainButton = document.querySelector('.try-again');
  tryAgainButton.addEventListener('click', () => {
    localStorage.removeItem('room');
    localStorage.removeItem('pickedColors');
    localStorage.removeItem('editedRoom');
    window.location.href = "color-change.php?step=1";
  });
});