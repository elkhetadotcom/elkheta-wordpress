(function(){
  var inputField = document.getElementById('vdo_client_key');
  var toggleKey = document.getElementById('toggle_API_visibility');
  var toggleSpan = document.getElementById('toggle_span');

  var currentState;

  window.onload = function(e){
    toggleSpanVis();
  }

  toggleKey.addEventListener('click', function(e){
    e.preventDefault();
    toggleVisibility(e);
  });

  inputField.addEventListener('keyup', function(e){
    toggleSpanVis();
  });

  function toggleSpanVis(){
    if (inputField.value == false) {
      toggleSpan.style.display = 'none';
      hideKey();
    }
    else {
      toggleSpan.style.display = 'inline-block';
    }
  }

  function toggleVisibility(e){
    currentState = e.target.getAttribute('data-protected');
    if (currentState == 'On'){
      showKey();
    }
    else {
      hideKey();
    }
  }

  function showKey(){
    toggleKey.setAttribute('data-protected', 'Off');
    toggleKey.innerHTML = 'Hide API Secret Key';
    inputField.type='text';
  }

  function hideKey(){
    toggleKey.setAttribute('data-protected', 'On');
    toggleKey.innerHTML = 'Show API Secret Key';
    inputField.type='password';
  }

}());
