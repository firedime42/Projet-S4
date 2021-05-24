(function () {

    /**
     * getDate() --> 1-31
     */

    const defaultOpt = Object.freeze({
        daysOfWeek: Object.freeze({
            monday: 'Mon',
            tuesday: 'Tue',
            wednesday: 'Wed',
            thursday: 'Thu',
            friday: 'Fri',
            saturday: 'Sat',
            sunday: 'Sun'
        }),
        months: Object.freeze([
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ]),
        dataColors: Object.freeze({
            negative: '#ff0000',
            neutral: '#ffffff',
            positive: '#0000ff'
        }),
        legendTitle: 'Legend'
    });

    /**
     * ------------------------------------------------------------------------
     *   Objet pour gérer les couleurs
     * ------------------------------------------------------------------------
     */
    
     function rgbaFromHex(hex) {
        return {
            r: parseInt(hex.slice(1, 3), 16),
            g: parseInt(hex.slice(3, 5), 16),
            b: parseInt(hex.slice(5, 7), 16),
            a: (hex.length > 8) ? parseInt(hex.slice(7, 9), 16) : 255
        }
    }
    
    function rgbaToHex(rgba) {
        let r = ('0' + rgba.r.toString(16)).slice(-2);
        let g = ('0' + rgba.g.toString(16)).slice(-2);
        let b = ('0' + rgba.b.toString(16)).slice(-2);
        let a = ('0' + rgba.a.toString(16)).slice(-2);
        return `#${r}${g}${b}${a}`;
    }

    
    function rgbToHCL({ r, g, b }) {
        let M = Math.max(r, g, b);
        let m = Math.min(r, g, b);
        let C = M - m;
        let L = (M + m) / 2;
        let H = 0;

        if (C == 0) H = 0;
        else if (M == r) H = ((g - b) / C) % 6;
        else if (M == g) H = (b - r) / C + 2;
        else if (M == b) H = (r - g) / C + 4;

        return { H, C, L };
    }

    function rgbFromHCL({ H, C, L }) {
        let X = C * (1 - Math.abs(H % 2 - 1));
        let m = L - C / 2;
        let color = null;

        if (C == 0) color = { r: 0, g: 0, b: 0 };
        else if (0 <= H && H < 1) color = { r: C, g: X, b: 0 };
        else if (1 <= H && H < 2) color = { r: X, g: C, b: 0 };
        else if (2 <= H && H < 3) color = { r: 0, g: C, b: X };
        else if (3 <= H && H < 4) color = { r: 0, g: X, b: C };
        else if (4 <= H && H < 5) color = { r: X, g: 0, b: C };
        else if (5 <= H && H < 6) color = { r: C, g: 0, b: X };

        color.r += m;
        color.g += m;
        color.b += m;

        return color;
    }

    class LinearGradientHCL {
        #from;
        #from_alpha;
        #to;
        #to_alpha;

        constructor(from, to) {
            let rgba_from = rgbaFromHex(from);
            let rgba_to = rgbaFromHex(to);
            this.#from = rgbToHCL(rgba_from);
            this.#to = rgbToHCL(rgba_to);
            this.#from_alpha = rgba_from.a;
            this.#to_alpha = rgba_to.a;

            if (this.#from.C == 0) this.#from.H = this.#to.H;
            else if (this.#to.C == 0) this.#to.H = this.#from.H;
        }

        getColorAt(x) {
            let H = x * this.#to.H + (1 - x) * this.#from.H;
            let C = x * this.#to.C + (1 - x) * this.#from.C;
            let L = x * this.#to.L + (1 - x) * this.#from.L;
            let alpha = Math.round(x * this.#to_alpha + (1 - x) * this.#from_alpha);
            let color = rgbFromHCL({ H, C, L });
            return rgbaToHex({ r: Math.round(color.r), g: Math.round(color.g), b: Math.round(color.b), a: alpha });
        }

        createGradient(nb_points) {
            var grad = new Array(nb_points);
            
            for (let i = 0; i < nb_points; i++)
                grad[i] = this.getColorAt(i / (nb_points - 1));

            return grad;
        }

    }

    /**
     * ----------------------------------------------------------------
     *  Retour au code
     * ----------------------------------------------------------------
     */


    /**
     * 
     * @param {*} year 
     * @param {*} month 
     * @returns 
     */
    function getNbDays(year, month) {
        return new Date(year, month + 1, 0).getDate();
    }

    function createChart(options) {
        var chart = document.createElement('div');
        chart.classList.add('calendar-chart');
        chart.innerHTML = `
            <div class="cc-header">
                <a class="cc-day">${options.daysOfWeek.monday}</a>
                <a class="cc-day">${options.daysOfWeek.tuesday}</a>
                <a class="cc-day">${options.daysOfWeek.wednesday}</a>
                <a class="cc-day">${options.daysOfWeek.thursday}</a>
                <a class="cc-day">${options.daysOfWeek.friday}</a>
                <a class="cc-day">${options.daysOfWeek.saturday}</a>
                <a class="cc-day">${options.daysOfWeek.sunday}</a>
            </div>
            <div class="cc-content">
            </div>
            <div class="cc-legend">
                <div class="cc-legend-header">${options.legendTitle}</div>
                <div class="cc-gradient"></div>
            </div>
        `;
        return chart;
    }

    function createMonth(date, options) {
        let d_month = new Date(date.getFullYear(), date.getMonth());
        let start_day = ([ 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])[d_month.getDay()];
        let num_month = d_month.getMonth();
        let num_year = d_month.getFullYear();
        let nb_days = getNbDays(num_year, num_month);

        var month = document.createElement('div');
        month.classList.add('cc-month');
        month.classList.add('cc-start-'+start_day);

        var header = document.createElement('div');
        header.classList.add('cc-month-header');
        header.innerText = `${options.months[num_month]} ${num_year}`;

        var days = document.createElement('div');
        days.classList.add('cc-month-days');

        for (let i = 0; i < nb_days; i++) {
            let day = document.createElement('a');
            day.classList.add('cc-day');
            days.appendChild(day);
        }

        month.appendChild(header);
        month.appendChild(days);

        return month;
    }

    function clearChart(chart) {
        chart.children[1].innerHTML = '';
    }

    function addData(months, [date, value], min_value, max_value, positive, negative, options) {
        let n_month = date.getMonth();
        let n_year = date.getFullYear();
        let n_date = date.getDate();
        let str_month = n_year + '-' + n_month;

        if (!months[str_month]) months[str_month] = createMonth(date, options);

        let color = (value > 0) ?
            positive.getColorAt(value / max_value) :
            negative.getColorAt(value / min_value); 

        months[str_month].children[1].children[n_date-1].style.setProperty('--cc-day-color', color);
        months[str_month].children[1].children[n_date-1].setAttribute('data-value', value);
    }

    class CalendarChart {
        #container;
        #chart;
        #options;
        #positive;
        #negative;

        constructor(element, options = {}) {
            this.#container = element;
            this.#options = { ...defaultOpt, ...options };

            this.#chart = createChart(this.#options);
            this.#container.appendChild(this.#chart);

            this.#positive = new LinearGradientHCL(this.#options.dataColors.neutral, this.#options.dataColors.positive);
            this.#negative = new LinearGradientHCL(this.#options.dataColors.neutral, this.#options.dataColors.negative);
        }

        /**
         * 
         * @param {Array} data : [ [date, value], [date, value], ... ]
         */
        setData(data) {
            // tri des données
            data.sort((a, b) => a[0] - b[0]);

            // clear les anciennes données
            clearChart(this.#chart);

            let months = {};
            let nb_data = data.length;
            let min_value = data[0][1];
            let max_value = data[0][1];

            // cherche le maximum et le minimum des données
            for (let i = 0; i < nb_data; i++) {
                if (data[i][1] < min_value) min_value = data[i][1];
                else if (data[i][1] > max_value) max_value = data[i][1]; 
            }

            // ajoutes les données
            for (let i = 0; i < nb_data; i++)
                addData(months, data[i], min_value, max_value, this.#positive, this.#negative, this.#options);
            
            months = Object.values(months);
            let nb_months = months.length;

            // ajoutes les mois
            for (let i = 0; i < nb_months; i++)
                this.#chart.children[1].appendChild(months[i]);

            // creation de la legende
            this.#chart.children[2].children[1].innerHTML = "";
            for (let i = 0; i < 5; i++) {
                let value = Math.round((i / 4) * max_value + (1 - i / 4) * min_value);
                let color = document.createElement("div");
                color.style.backgroundColor = (value > 0) ?
                    this.#positive.getColorAt(value / max_value) :
                    this.#negative.getColorAt(value / min_value);
                color.innerText = value;
                this.#chart.children[2].children[1].appendChild(color);
            }
        }
    }

    window.CalendarChart = CalendarChart;
})();