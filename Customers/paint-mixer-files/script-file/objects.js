class RoomObject {
  #paths;

  constructor(paths, fillColor = "rgb(0,0,0)", strokeColor = "rgb(0,0,0)", lineWidth = 0) {
    this.#paths = paths;
    this.fillColor = fillColor;
    this.strokeColor = strokeColor;
    this.lineWidth = lineWidth;
  }

  getPaths() {
    return this.#paths;
  }

  draw(ctx) {
    ctx.beginPath();
    ctx.moveTo(this.#paths[0].x, this.#paths[0].y);
    for (let i = 1; i < this.#paths.length; ++i) {
      ctx.lineTo(this.#paths[i].x, this.#paths[i].y);
    }
    ctx.fillStyle = this.fillColor;
    ctx.strokeStyle = this.strokeColor;
    ctx.lineWidth = this.lineWidth;
    ctx.fill();
    ctx.closePath();
  }

  isPointed(x, y) {
    let inside = false;
    for (let i = 0, j = this.#paths.length - 1; i < this.#paths.length; j = i++) {
      const xi = this.#paths[i].x, yi = this.#paths[i].y;
      const xj = this.#paths[j].x, yj = this.#paths[j].y;

      const intersect = ((yi > y) !== (yj > y)) &&
        (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
      if (intersect) inside = !inside;
    }
    return inside;
  }

  createCopy() {
    return new RoomObject(this.#paths, this.fillColor, this.strokeColor, this.lineWidth);
  }

  static drawRoomObject(ctx, object, fill = "rgb(0,0,0)", stroke = "rgb(0,0,0)", lineWidth = 0) {
    const paths = object.getPaths();
    ctx.beginPath();
    ctx.moveTo(paths[0].x, paths[0].y);
    for (let i = 1; i < paths.length; ++i) {
      ctx.lineTo(paths[i].x, paths[i].y);
    }
    ctx.fillStyle = fill;
    ctx.strokeStyle = stroke;
    ctx.lineWidth = lineWidth;
    ctx.fill();
    ctx.closePath();
  }
};


class RoomObjectGroup {
  constructor(objects, width, height, defaultColor) {
    this.objects = objects;
    this.width = width;
    this.height = height;
    this.defaultColor = defaultColor;
  }

  createCopy() {
    const copiedObj = {};
    Object.keys(this.objects).forEach(key => {
      copiedObj[key] = this.objects[key].createCopy();
    });

    return new RoomObjectGroup(copiedObj, this.width, this.height, this.defaultColor);
  }

  setColorToDefault() {
    Object.values(this.objects).forEach(obj => {
      obj.fillColor = this.defaultColor;
    });
  }
}

// Identify the parts in living room image that can be fill with colors
function createLivingRoomObjects(width, height) {
  const defaultColor = "rgb(227, 227, 227)";
  function relativeCoor(x, y) {
    const X = x * width;
    const Y = y * height;
    return { x: X, y: Y };
  }

  //Living room Object
  const wall = new RoomObject([
    relativeCoor(0.3016666666666667, 0),
    relativeCoor(0.3016666666666667, 0.003809521993001302),
    relativeCoor(0.295, 0.009142855326334635),
    relativeCoor(0.295, 0.6586666666666666),
    relativeCoor(0, 0.6586666666666666),
    relativeCoor(0, 0.88),
    relativeCoor(1.2007143147786459, 0.88),
    relativeCoor(1.2007143147786459, 0.6586666666666666),
    relativeCoor(0.7166666666666667, 0.6586666666666666),
    relativeCoor(0.7166666666666667, 0.009142855326334635),
    relativeCoor(0.7133333333333334, 0.003809521993001302),
    relativeCoor(0.7133333333333334, 0),
    relativeCoor(0.45523810068766274, 0),
  ], defaultColor);

  const leftWindowBorder = new RoomObject([
    relativeCoor(0, 0),
    relativeCoor(0.3016666666666667, 0),
    relativeCoor(0.3016666666666667, 0.003276188659667969),
    relativeCoor(0.295, 0.008609521993001301),
    relativeCoor(0.295, 0.6586666666666666),
    relativeCoor(0, 0.6586666666666666),
    relativeCoor(0, 0),
  ], defaultColor);

  const rightWindowBorder = new RoomObject([
    relativeCoor(0.7133333333333334, 0),
    relativeCoor(0.7133333333333334, 0.003809521993001302),
    relativeCoor(0.7166666666666667, 0.009142855326334635),
    relativeCoor(0.7166666666666667, 0.6586666666666666),
    relativeCoor(1.2007143147786459, 0.6586666666666666),
    relativeCoor(1.2007143147786459, 0),
    relativeCoor(0.7133333333333334, 0),
  ], defaultColor);

  return new RoomObjectGroup({ wall, leftWindowBorder, rightWindowBorder }, width, height, defaultColor);
}

// Identify the parts in bed room image that can be colored 
function createBedRoomObjects(width, height) {
  const defaultColor = "rgb(210, 210, 210)";
  function relativeCoor(x, y) {
    const X = x * width;
    const Y = y * height;
    return { x: X, y: Y };
  }

  //Bed room Object
  const leftWall = new RoomObject([
    relativeCoor(0, 0),
    relativeCoor(0.19466666666666665, 0),
    relativeCoor(0.19466666666666665, 0.7866666666666666),
    relativeCoor(0, 0.8533333333333334),
  ], defaultColor);

  const rightWall = new RoomObject([
    relativeCoor(0.195, 0),
    relativeCoor(1, 0),
    relativeCoor(1, 1.0133333333333334),
    relativeCoor(0.855, 1.0133333333333334),
    relativeCoor(0.855, 0.18666666666666668),
    relativeCoor(0.5666666666666667, 0.224),
    relativeCoor(0.5666666666666667, 1.0666666666666667),
    relativeCoor(0.195, 0.7866666666666666),
    relativeCoor(0.195, 0),
  ], defaultColor);

  const windowBorder = new RoomObject([
    relativeCoor(0.855, 0.1856),
    relativeCoor(0.5669166666666666, 0.22293333333333332),
    relativeCoor(0.5669166666666666, 0.5333333333333333),
    relativeCoor(0.8546666666666666, 0.5333333333333333),
    relativeCoor(0.8546666666666666, 0.18666666666666668),
  ], defaultColor);

  return new RoomObjectGroup({ leftWall, rightWall, windowBorder }, width, height, defaultColor);
}

class VisualizerHistory {
  #states;
  #redoStates;

  constructor() {
    this.#states = [];
    this.#redoStates = [];
  }

  clear() {
    this.#states = [];
    this.#redoStates = [];
  }

  addState(state) {
    const copiedState = state;
    this.#redoStates = [];
    this.#states.push(copiedState);
  }

  undo() {
    if (this.#states.length <= 1) {
      return null;
    }

    const state = this.#states.pop();
    this.#redoStates.push(state);

    const currentState = this.#states[this.#states.length - 1];
    return currentState;
  }

  redo() {
    if (this.#redoStates.length <= 0) {
      return null;
    }

    const state = this.#redoStates.pop();
    this.#states.push(state);

    const currentState = this.#states[this.#states.length - 1];
    return currentState;
  }

}



// this contains the functionalities and controls for image
function createVisualizer() {
  const images = {
    "living-room": "./paint-mixer-files/images/living-room.png",
    "bedroom": "./paint-mixer-files/images/bedroom.png",
  };
  const imageKeys = Object.keys(images);
  let currentColor;
  const canvas = document.getElementById("visualizer-canvas");
  const ctx = canvas.getContext("2d");
  const img = new Image();

  const zw = 7, zh = 7;
  const zoomCanvas = document.getElementById("zoomCanvas");
  const zoomCtx = zoomCanvas?.getContext("2d");

  /**
   * @var defaultPixelData use to store the default image data to use later
   * @var currentState store the objects that identify the parts of image 
   */

  const history = new VisualizerHistory();
  let defaultPixelData;
  let currentState;
  const objectOpacity = 235;

  function initCurrentState(key) {
    if (key === imageKeys[0]) {
      currentState = createLivingRoomObjects(canvas.width, canvas.height);
    } else if (key === imageKeys[1]) {
      currentState = createBedRoomObjects(canvas.width, canvas.height);
    }
    history.addState(currentState.createCopy());
  }

  function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }

  function drawObjects() {
    Object.values(currentState.objects).forEach((obj) => {
      obj.draw(ctx);
    });
  }

  // This is use to display the image with the modified colors
  function render() {
    clearCanvas();
    drawObjects();
    ctx.drawImage(img, 0, 0);
  }

  function setImage(imageKey) {
    return new Promise((resolve, reject) => {
      const imageURL = images[imageKey];
      img.addEventListener('load', () => {
        canvas.width = img.width;
        canvas.height = img.height;

        ctx.drawImage(img, 0, 0);
        defaultPixelData = new Uint8ClampedArray(ctx.getImageData(0, 0, canvas.width, canvas.height).data);

        initCurrentState(img.__imageKey);
        render();
        resolve();
      }, { once: true });

      img.onerror = () => {
        reject(new Error("Failed To load image"));
      }

      img.crossOrigin = "Anonymous";
      img.src = imageURL;
      img.__imageKey = imageKey;
    });
  }

  function setColor(color) {
    currentColor = color;
  }

  // When the object is hovered this will execute
  function hoverEffect(event) {
    const rect = canvas.getBoundingClientRect();
    const mouseX = Math.floor(event.clientX - rect.left);
    const mouseY = Math.floor(event.clientY - rect.top);
    const pixelIndex = (mouseY * canvas.width + mouseX) * 4;
    render();
    // const r = defaultPixelData[pixelIndex];
    // const g = defaultPixelData[pixelIndex+1];
    // const b = defaultPixelData[pixelIndex+2];
    const a = defaultPixelData[pixelIndex+3];

    zoomCanvas.classList.remove("hide");
    zoomCanvas.style.left = event.clientX - zw * 4 + 'px';
    zoomCanvas.style.top = event.clientY - 100 + 'px';
    zoomCanvas.width = zw;
    zoomCanvas.height = zh;

    const zoomPixelData = ctx.getImageData(mouseX - 3, mouseY - 3, zw, zh);
    zoomCtx.clearRect(0, 0, zoomCanvas.width, zoomCanvas.height);
    zoomCtx.putImageData(zoomPixelData, 0, 0);

    // If alpha or opacity is greater than 235 (opaque), prevent hover
    if (a >= objectOpacity) {
      return;
    }

    // Iterate through each objects that been identified in the image and change the add a color on the top of it if it is hovered
    Object.values(currentState.objects).forEach((obj) => {
      if (obj.isPointed(mouseX, mouseY)) {
        clearCanvas();
        drawObjects();
        RoomObject.drawRoomObject(ctx, obj, "rgba(140,140,255,0.6)");
        ctx.drawImage(img, 0, 0);
      }
    });
  }

  // This is use to change the oclor of identified object in the image
  function fillObject(event) {
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const pixelIndex = (y * canvas.width + x) * 4;

    // If alpha or opacity is greater than 9.0 prevent hover
    if (defaultPixelData[pixelIndex + 3] >= objectOpacity) {
      return;
    }

    // Iterate through each object that benn identified in the image and change the color of the clicked object or part
    Object.values(currentState.objects).forEach((obj) => {
      if (obj.isPointed(x, y)) {
        obj.fillColor = currentColor;
        history.addState(currentState.createCopy());
        localStorage.setItem('editedRoom', JSON.stringify(currentState));
      }
    });

    render();
  }

  function reset() {
    history.clear();
    currentState.setColorToDefault();
    history.addState(currentState.createCopy());
    localStorage.setItem('editedRoom', JSON.stringify(currentState));
    render();
  }

  function undo() {
    const prevState = history.undo();
    if (!prevState) {
      return;
    }
    currentState = prevState.createCopy();
    render();
  }

  function redo() {
    const nextState = history.redo();
    if (!nextState) {
      return;
    }
    currentState = nextState.createCopy();
    render();
  }

  function hoverOff() {
    if (!zoomCanvas.classList.contains("hide")) {
      zoomCanvas.classList.add("hide");
    }
    render();
  }

  function order() {
    window.location.href = "color-change.php?step=4";
  }

  function fillImage(obj) {
    Object.keys(currentState.objects).forEach((key) => {
      currentState.objects[key].fillColor = obj[key].fillColor;
    });
    render();
  }

  return {
    setImage,
    reset,
    undo,
    redo,
    setColor,
    imageKeys,
    order,
    canvas,
    hoverEffect,
    hoverOff,
    fillObject,
    render,
    fillImage,
  };
}


function downloadCanvasImage(img_name) {
  const canvas = document.getElementById("visualizer-canvas");
  const dataURL = canvas.toDataURL();

  const link = document.createElement('a');
  link.href = dataURL;
  link.download = img_name;
  document.body.appendChild(link);

  link.click();

  document.body.removeChild(link);
}

function downloadJSON(jsonName, jsonData) {
  console.log(jsonData);
  const blob = new Blob([JSON.stringify(jsonData)], { type: 'application/json' });

  const link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);
  link.download = jsonName;
  document.body.appendChild(link);

  link.click();

  document.body.removeChild(link);
}

function saveProject() {
  const jsonData = {
    'pickedColors': localStorage.getItem('pickedColors'),
    'room': localStorage.getItem('room'),
    'editedRoom': localStorage.getItem('editedRoom'),
  }

  const filteredData = {};
  for (const key in jsonData) {
    if (jsonData.hasOwnProperty(key) && jsonData[key] !== undefined) {
      filteredData[key] = jsonData[key];
    }
  }

  downloadJSON("data.json", filteredData);
  downloadCanvasImage("painted-project.png");
}

function loadProjectFromJSON(jsonData) {
  const parsedData = JSON.parse(jsonData);

  const pickedColors = parsedData.pickedColors;
  const room = parsedData.room;
  const editedRoom = parsedData.editedRoom;

  localStorage.setItem('pickedColors', pickedColors);
  localStorage.setItem('room', room);
  localStorage.setItem('editedRoom', editedRoom);
}

function handleFileInputChange(event) {
  const file = event.target.files[0];
  const reader = new FileReader();

  reader.addEventListener('load', function (event) {
    const jsonData = event.target.result;
    loadProjectFromJSON(jsonData);
  }, { once:true });

  reader.readAsText(file);
}


