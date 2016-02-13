<?php

class GetGeneralData extends BaseAPIController {
    function get_xhr() {
        if ($this->checkAuth()) {

            $data = json_decode('[
                  {"id":"random1", "title": "Number of Transactions", "type":2, "size":1, "icon":"area-chart", "refreshRate": 30, "refreshURL": "/assets/js/new.json", "refreshAdd": true, "data": {
                    "element": "data-random1",
                    "data": [
                      {"label": "2016-02-24 11:00", "value": 20},
                      {"label": "2016-02-24 12:00", "value": 10},
                      {"label": "2016-02-24 13:00", "value": 5},
                      {"label": "2016-02-24 14:00", "value": 8},
                      {"label": "2016-02-24 15:00", "value": 3},
                      {"label": "2016-02-24 16:00", "value": 5}
                    ],
                    "xkey": "label",
                    "ykeys": ["value"],
                    "labels": ["Transactions"]
                  }},
                  {"id":"random2", "title": "Most Traded Securities", "type":1, "size":0, "icon":"bar-chart", "refreshRate": 60, "refreshURL": "/assets/js/new.json", "refreshAdd": false, "data": {
                    "element": "data-random2",
                    "data": [
                      {"label": "B of A", "value": 20},
                      {"label": "Google", "value": 22},
                      {"label": "Yahoo", "value": 50},
                      {"label": "Equibits", "value": 75},
                      {"label": "EDC", "value": 240}
                    ],
                    "xkey": "label",
                    "ykeys": ["value"],
                    "labels": ["# of Trades"],
                    "parseTime": false
                  }},
                  {"id":"random3", "title": "Best Performers", "type":3, "size":0, "icon":"line-chart", "data": {
                    "element": "data-random3",
                    "data": [
                      {"label": "B of A", "value": 20},
                      {"label": "Google", "value": 22},
                      {"label": "Yahoo", "value": 50},
                      {"label": "Equibits", "value": 75},
                      {"label": "EDC", "value": 240}
                    ],
                    "xkey": "label",
                    "ykeys": ["value"],
                    "labels": ["# of Trades"],
                    "parseTime": false
                  }},
                  {"id":"random4", "title": "Best Performers Donut", "type":4, "icon":"pie-chart", "size":0, "data": {
                    "element": "data-random4",
                    "data": [
                      {"label": "Download Sales", "value": 12},
                      {"label": "In-Store Sales", "value": 30},
                      {"label": "Mail-Order Sales", "value": 20}
                    ]
                  }},
                  {"id":"random5", "title": "data Table Display", "type":5, "size":0, "icon":"table", "data": {
                    "data": [
                      {
                        "name":       "Tiger Nixon",
                        "position":   "System Architect",
                        "salary":     "$3,120",
                        "ext_#":      "5421"
                      },
                      {
                        "name":       "Garrett Winters",
                        "position":   "Director",
                        "salary":     "$5,300",
                        "ext_#":      "8422"
                      },
                      {
                        "name":       "Another Name",
                        "position":   "Toilet Cleaner",
                        "salary":     "$1,300",
                        "ext_#":      ""
                      },
                      {
                        "name":       "Garrett Winters",
                        "position":   "Director",
                        "salary":     "$5,300",
                        "ext_#":      "8422"
                      },
                      {
                        "name":       "Another Name",
                        "position":   "Toilet Cleaner",
                        "salary":     "$1,300",
                        "ext_#":      ""
                      },
                      {
                        "name":       "Another Name",
                        "position":   "Toilet Cleaner",
                        "salary":     "$1,300",
                        "ext_#":      ""
                      },
                      {
                        "name":       "Garrett Winters",
                        "position":   "Director",
                        "salary":     "$5,300",
                        "ext_#":      "8422"
                      },
                      {
                        "name":       "Another Name",
                        "position":   "Toilet Cleaner",
                        "salary":     "$1,300",
                        "ext_#":      ""
                      }
                    ],
                    "columns": [
                      { "data": "name" },
                      { "data": "position" },
                      { "data": "salary" },
                      { "data": "ext_#" }
                    ]
                  }}]');
            echo json_encode(StatusReturn::S200($data));
        } else {
            echo json_encode(StatusReturn::E401());
        }
    }
}