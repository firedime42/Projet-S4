
(function () {
    const SEC  = 1000;
    const MIN  = 60 * SEC;
    const HOUR = 60 * MIN;
    const DAY  = 24 * HOUR;
    const MONTH = 31 * DAY;
    const YEAR = 365 * DAY;

    AutoTime.modes["custom"] = function (element, now, time) {
        
        const today = new Date(now);
        const yesterday = new Date(now - DAY);

        let dtime = now - time;
        let date = new Date(time);
        let d_hours = date.getHours();
        let d_minutes = `0${date.getMinutes()}`.slice(-2);
        let d_day = `0${date.getDate()}`.slice(-2);
        let d_month = `0${date.getMonth()}`.slice(-2);
        let d_year = date.getFullYear();

        var res;
        
        if (dtime < HOUR)
            res = `Il y a ${Math.floor(dtime / MIN)} min.`;
        else if (today.sameDay(date))
            res = `Ajourd'hui à ${d_hours}:${d_minutes}.`;
        else if (yesterday.sameDay(date))
            res = `Hier à ${d_hours}:${d_minutes}.`;
        else
            res = `Le ${d_day}/${d_month}/${d_year} à ${d_hours}:${d_minutes}.`;
    
        return res;
    };

    AutoTime.modes["delais_minutes"] = function (element, now, time) {
        let nb_secondes = Math.floor((time % MIN) / SEC);
        let nb_minutes = Math.floor((time % HOUR) / MIN);
        let nb_hours = Math.floor((time % DAY) / HOUR);
        let nb_days = Math.floor(time / DAY);

        return `${nb_days}j ${nb_hours}h ${nb_minutes}m ${nb_secondes}s`;
    };

    AutoTime.modes["since"] = function (element, now, time) {
        const today = new Date(now);
        const day = new Date(time);
        const dtime = today - time;

        let unit = "secondes"; // Chaqun
        let since = 0;
        
        if (dtime > YEAR) unit = "an", since = today.getFullYear() - day.getFullYear();
        else if (dtime > MONTH) unit = "mois", since = (12 + today.getMonth() - day.getMonth()) % 12;
        else if (dtime > DAY) unit = "jour", since = Math.floor(dtime / DAY);
        else if (dtime > HOUR) unit = "heure", since = Math.floor(dtime / HOUR);
        else unit = "minute", since = Math.floor(dtime / MIN);

        let s = (unit == 'mois' || since > 1) ? "s" : "";

        return `${since} ${unit}${s}`;
    };
})();