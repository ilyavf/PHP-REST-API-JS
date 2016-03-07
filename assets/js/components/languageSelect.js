define(['config'], function(config) {

  return {
    languages: config.languages,
    switchLang: function(code) {
      localStorage.setItem('locale', code);
      location.reload();
      return false;
    }

  };
});