(function(){

var vdoTheme = document.getElementById('vdo_player_theme_options');

var vdoThemeArr = [
  [
    document.getElementById('default1-image'),
    document.getElementById('default2-image'),
    document.getElementById('default3-image'),
    document.getElementById('color1-image'),
    document.getElementById('color2-image'),
    document.getElementById('color3-image'),
    document.getElementById('square1-image'),
    document.getElementById('square2-image'),
    document.getElementById('square3-image'),
  ],
  [
    document.getElementById('default1-btn'),
    document.getElementById('default2-btn'),
    document.getElementById('default3-btn'),
    document.getElementById('color1-btn'),
    document.getElementById('color2-btn'),
    document.getElementById('color3-btn'),
    document.getElementById('square1-btn'),
    document.getElementById('square2-btn'),
    document.getElementById('square3-btn'),
  ],
  [
    document.getElementById('default1-id').innerHTML,
    document.getElementById('default2-id').innerHTML,
    document.getElementById('default3-id').innerHTML,
    document.getElementById('color1-id').innerHTML,
    document.getElementById('color2-id').innerHTML,
    document.getElementById('color3-id').innerHTML,
    document.getElementById('square1-id').innerHTML,
    document.getElementById('square2-id').innerHTML,
    document.getElementById('square3-id').innerHTML,
  ],
];

var vdoColors = [
'#0294f9',
'#CFC10A',
'#67AF17',
]

var vdoThemeNumber = vdoThemeArr[0].length;

window.onload = function(){
  vdoThemeSelect(vdoThemeArr[2].indexOf(vdoTheme.value));
  vdoTheme.addEventListener('change', vdoThemeManualInput)
  for (var k = 0; k < vdoThemeNumber; k++){
    vdoThemeArr[0][k].addEventListener('click', vdoThemeImageClick);
    vdoThemeArr[1][k].addEventListener('click', vdoThemeBtnClick);
  }
}

function vdoThemeImageClick(e){
  vdoThemeSelect(vdoThemeArr[0].indexOf(e.target));
}

function vdoThemeBtnClick(e){
  vdoThemeSelect(vdoThemeArr[1].indexOf(e.target));
}

function vdoThemeSelect(index){
  for (var j = 0; j < vdoThemeNumber; j++) {
    if (j===index){
      vdoTheme.value = vdoThemeArr[2][j];
      vdoThemeArr[0][j].style.borderWidth="10px";
      vdoThemeArr[0][j].style.borderStyle="solid";
      vdoThemeArr[0][j].style.borderColor="#4CB78C";
      vdoThemeArr[0][j].style.width="92%";
      vdoThemeArr[1][j].innerHTML = "Selected";
      vdoThemeArr[1][j].style.backgroundColor = "#0f9";
    }
    else {
      vdoThemeArr[0][j].style.border="";
      vdoThemeArr[0][j].style.width="100%";
      vdoThemeArr[1][j].innerHTML = "Select";
      vdoThemeArr[1][j].style.backgroundColor = "#fff";
    }
  }
}

function vdoThemeManualInput(e){
  vdoThemeSelect(vdoThemeArr[2].indexOf(e.target.value));
}
}());


