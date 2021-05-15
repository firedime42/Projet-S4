/**
 * Permettre aux dates sur la page d'être automatiquement mis à jours
 * @author Mattéo Mezzasalma
 * @required Dom.js
 */

(function () {
    const MIN_TTME_BETWEEN_UPDATE = 500;
    const requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame;

    var last_time_update = 0;

    var modes = {
        "default": function (e, now, time) {
            let date = new Date(time);
            let d_hours = date.getHours();
            let d_minutes = `0${date.getMinutes()}`.slice(-2);
            let d_day = date.getDate();
            let d_month = date.getMonth();
            let d_year = date.getFullYear();
            return `${d_day}/${d_month}/${d_year} ${d_hours}:${d_minutes}`;
        }
    };

    function updateTime(now) {
        let elements = Dom.find('span.autotime[data-mode]');
        let nb_elements = elements.length;

        for (let i = 0; i < nb_elements; i++) {
            try {
                let element = elements[i];
                let mode_name = Dom.attribute(element, 'data-mode');
                let time = Dom.attribute(element, 'data-time') * 1 || now;
                let mode = modes[mode_name] || modes["default"];
                let new_text = mode(element, now, time);
                let old_text = Dom.text(element);
                if (old_text != new_text) Dom.text(element, new_text);
            } catch (e) { console.error(e); }
        }
    }

    function autoUpdate() {
        let time = Date.now();
        
        if (time - last_time_update > MIN_TTME_BETWEEN_UPDATE) {
            last_time_update = time;

            updateTime(time);
        }
        
        requestAnimationFrame(autoUpdate);
    }
    autoUpdate();

    window.AutoTime = Object.freeze({
        modes,
        createHTML: function (mode, time) {
            return `<span class="autotime" data-mode="${mode}" data-time="${time}"></span>`;
        }
    });// don't tuch AutoTime
})();