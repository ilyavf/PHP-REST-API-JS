define(function() {
  return {
    languages: [
      {name: "English", code: "en"},
      {name: "Français", code: "fr"}
    ],
    server: {
      domain: "http://edc2.auto-cms.com"
    },
    barDefaults: {
      barColors: ['#DA650E'],
      resize: true
    },
    areaDefaults: {
      fillOpacity: 0.5,
      lineColors: ['#41A69B'],
      pointFillColors:['#ffffff'],
      pointStrokeColors: ['black'],
      resize: true
    },
    lineDefaults: {
      lineColors: ['#41A69B'],
      pointFillColors:['#ffffff'],
      pointStrokeColors: ['black'],
      resize: true
    },
    donutDefaults: {
      colors: ['#66D654','#0F7000','#23B40C','#44C82F','#159102'],
      resize: true
    },
    dtDefaults: {
      pageLength: 5
    }
  }
});