import { Component, OnInit, Input } from '@angular/core';
import { cookie_options } from '../core/models/cookie_options.model';
import { cookie } from '../core/models/cookie.model';
import { IdbService } from '../core/idb.service';
import { HttpClient } from '@angular/common/http';
import { CookieService } from 'ngx-cookie-service';
import { Subject, Subscription, Observable } from 'rxjs';

@Component({
  selector: 'app-cookie',
  templateUrl: './cookie.component.html',
  styleUrls: ['./cookie.component.css']
})
export class CookieComponent implements OnInit {
    @Input() options = new cookie_options();
    _ec = new cookie();

    idb_subject = new Subject<string>();
    idb_sub = Subscription;

    cookie_subject = new Subject<string>();
    cookie_sub = Subscription;

    local_storage_subject = new Subject<string>();
    local_storage_sub = Subscription;

    session_subject = new Subject<string>();
    session_sub = Subscription;

    window_subject = new Subject<string>();
    window_sub = Subscription;

    png_subject = new Subject<string>();
    png_sub = Subscription;

    etag_subject = new Subject<string>();
    etag_sub = Subscription;

    cache_subject = new Subject<string>();
    cache_sub = Subscription;

    constructor(private idb_service: IdbService,
                private http: HttpClient,
                private cookie_service: CookieService) { }

    ngOnInit(){
        this.idb_subject.subscribe();
        this.cookie_subject.subscribe();
        this.local_storage_subject.subscribe();
        this.session_subject.subscribe();
        this.window_subject.subscribe();
        this.png_subject.subscribe();
        this.etag_subject.subscribe();
        this.cache_subject.subscribe();
    }

    get(name: string, cb: Function, dont_reset: number){
        this._evercookie(name, cb, undefined, undefined, dont_reset);
    }

    set(name: string, value: string){
        this._evercookie(name, function () {}, value, undefined, undefined);
    }

    async _evercookie(name: string, cb: Function, value: string, i: number, dont_reset: number){
        if (i === undefined) {
            i = 0;
        }
        // first run
        if (i === 0) {
            if (this.options.idb) {
                const idb_prom = this.evercookie_indexdb_storage(name, value);
            }
            if (this.options.pngCookieName) {
                const png_prom = this.evercookie_png(name, value);
            }
            if (this.options.etagCookieName) {
                const etag_prom = this.evercookie_etag(name, value);
            }
            if (this.options.cacheCookieName) {
                const cache_prom = this.evercookie_cache(name, value);
            }

            const cookie_prom = this.evercookie_cookie(name, value);
            const local_stor_prom = this.evercookie_local_storage(name, value);
            const session_stor_prom = this.evercookie_session_storage(name, value);
            const window_prom = this.evercookie_window(name, value);

            const arar = await Promise.all([idb_prom,png_prom,etag_prom,cache_prom,cookie_prom,local_stor_prom,session_stor_prom,window_prom]);
        }

        // writing data
        if (value !== undefined) {
            {
                setTimeout(function () {
                    this._evercookie(name, cb, value, i, dont_reset);
                }, 300);
            }
        }
        // when reading data, we need to wait for swf, db, silverlight, java and png
        else
        {
            if (
                (   // we support local db and haven't read data in yet
                    (this.options.idb && (typeof this._ec.idbData === "undefined" || this._ec.idbData === "")) ||
                    (this.options.etagCookieName && typeof this._ec.etagData === "undefined") ||
                    (this.options.cacheCookieName && typeof this._ec.cacheData === "undefined") ||
                    (this.options.pngCookieName && document.createElement("canvas").getContext && (typeof this._ec.pngData === "undefined" || this._ec.pngData === ""))
                ) &&
                i++ < this.options.tests
            )
            {
                setTimeout(function () {
                    this._evercookie(name, cb, value, i, dont_reset);
                }, 300);
            }

            // we hit our max wait time or got all our data
            else
            {
                var tmpec: cookie = this._ec,
                    candidates: number[] = [],
                    bestnum = 0,
                    candidate: string,
                    item: string;
                this._ec = {};

                // figure out which is the best candidate
                for (item in tmpec) {
                    if (tmpec[item] && tmpec[item] !== "null" && typeof tmpec[item] !== "undefined") {
                        candidates[tmpec[item]] = candidates[tmpec[item]] === undefined ? 1 : candidates[tmpec[item]] + 1;
                    }
                }

                for (item in candidates) {
                    if (candidates[item] > bestnum) {
                        bestnum = candidates[item];
                        candidate = item;
                    }
                }

                // reset cookie everywhere
                if (candidate !== undefined && (dont_reset === undefined || dont_reset !== 1)) {
                    this.set(name, candidate);
                }
                if (typeof cb === "function") {
                    cb(candidate, tmpec);
                }
            }
        }
    }

    async evercookie_window(name: string, value: string) {
        if (value !== undefined) {
            window.name = this._ec_replace(window.name, name, value);
            return this.getFromStr(name, window.name);
            // this.window_subject.next(this.getFromStr(name, window.name));
        } else {
            return this.getFromStr(name, window.name);
            // this.window_subject.next(this.getFromStr(name, window.name));
        }
    }

    async evercookie_cache(name: string, value: string) {
        if (value !== undefined) {
            // make sure we have evercookie session defined first
            this.cookie_service.set(this.options.cacheCookieName, value, undefined, "/", this.options.domain);
            this.http.get(this.options.baseurl + this.options.phpuri + this.options.cachePath + "?name=" + name + "&cookie=" + this.options.cacheCookieName, {headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'}})
                .subscribe((response: string) => this.cache_subject.next(response) );
        } else {
            // interestingly enough, we want to erase our evercookie
            // http cookie so the php will force a cached response
            var orig_value = this.cookie_service.get(this.options.cacheCookieName);
            this._ec.cacheData = undefined;
            this.cookie_service.set(this.options.cacheCookieName, '', new Date(2020,1,1,0,0,0,0), "/", this.options.domain);
            this.http.get(this.options.baseurl + this.options.phpuri + this.options.cachePath + "?name=" + name + "&cookie=" + this.options.cacheCookieName)
                .subscribe(
                    (response: string) => {
                        this.cookie_service.set(this.options.cacheCookieName, orig_value, new Date(2040,12,31), "/", this.options.domain);
                        this.cache_subject.next(response);
                    }
                );
        }
    }

    async evercookie_etag(name: string, value: string) {
        if (value !== undefined) {
            // make sure we have evercookie session defined first
            this.cookie_service.set(this.options.etagCookieName, value, undefined, "/", this.options.domain);
            this.http.get(this.options.baseurl + this.options.phpuri + this.options.etagPath + "?name=" + name + "&cookie=" + this.options.etagCookieName)
                .subscribe((response: string) => this.etag_subject.next(response) );
        } else {
            // interestingly enough, we want to erase our evercookie
            // http cookie so the php will force a cached response
            var orig_value = this.cookie_service.get(this.options.etagCookieName);
            this._ec.etagData = undefined;
            this.cookie_service.set(this.options.etagCookieName, undefined, new Date(2010,9,20), "/", this.options.domain);
            this.http.get(this.options.baseurl + this.options.phpuri + this.options.etagPath + "?name=" + name + "&cookie=" + this.options.etagCookieName)
                .subscribe(
                    (response: string) => {
                        this.cookie_service.set(this.options.etagCookieName, orig_value, new Date(2040,12,31), "/", this.options.domain);
                        this.etag_subject.next(response);
                    }
                );
        }
    }

    async evercookie_png(name: string, value: string) {
        var canvas: HTMLCanvasElement = document.createElement("canvas"),
            img: CanvasImageSource, ctx: CanvasRenderingContext2D, origvalue: string;
        canvas.style.visibility = "hidden";
        canvas.style.position = "absolute";
        canvas.width = 200;
        canvas.height = 1;
        if (canvas && canvas.getContext) {
            // {{this.options.pngPath}} handles the hard part of generating the image
            // based off of the http cookie and returning it cached
            img = new Image();
            img.style.visibility = "hidden";
            img.style.position = "absolute";

            const png_promise = new Promise(resolve => resolve(true));
            png_promise.then

            if (value !== undefined) {
                // make sure we have evercookie session defined first
                this.cookie_service.set(this.options.pngCookieName, value, undefined, "/", this.options.domain);
                this.png_subject.next(value);
            } else {
                ctx = canvas.getContext("2d");

                // interestingly enough, we want to erase our evercookie
                // http cookie so the php will force a cached response
                origvalue = this.cookie_service.get(this.options.pngCookieName);
                this.cookie_service.set(this.options.pngCookieName, undefined, new Date(2010,9,20),"/",this.options.domain);

                img.onload = () => {
                    // put our cookie back
                    this.cookie_service.set(this.options.pngCookieName, value, new Date(2040,12,31), "/", this.options.domain);

                    let pngData: string = "";
                    ctx.drawImage(img, 0, 0);

                    // get CanvasPixelArray from  given coordinates and dimensions
                    var imgd = ctx.getImageData(0, 0, 200, 1),
                        pix = imgd.data, i: number, n: number;

                    // loop over each pixel to get the "RGB" values (ignore alpha)
                    for (i = 0, n = pix.length; i < n; i += 4) {
                        if (pix[i] === 0) {
                            break;
                        }
                        pngData += String.fromCharCode(pix[i]);
                        if (pix[i + 1] === 0) {
                            break;
                        }
                        pngData += String.fromCharCode(pix[i + 1]);
                        if (pix[i + 2] === 0) {
                            break;
                        }
                        pngData += String.fromCharCode(pix[i + 2]);
                    }

                    this.png_subject.next(pngData);
                };
            }
        img.src = this.options.baseurl + this.options.phpuri + this.options.pngPath + "?name=" + name + "&cookie=" + this.options.pngCookieName;
        img.crossOrigin = 'Anonymous';
        }
    }

    async evercookie_local_storage(name: string, value: string) {
        if (localStorage) {
            if (value !== undefined) {
                localStorage.setItem(name, value);
                return localStorage.getItem(name);
                // this.local_storage_subject.next(localStorage.getItem(name));
            } else {
                return localStorage.getItem(name);
                // this.local_storage_subject.next(localStorage.getItem(name));
            }
        }
    }

    async evercookie_indexdb_storage(name: string, value: string) {
        this.idb_service.connect_to_idb();
        if(value !== undefined){
            this.idb_service.add_cookie(value);
        } else {
            this.idb_service.get_cookie().then((value: string) =>{
                if(value === undefined) {
                    this._ec.idbData = undefined;
                } else {
                    this._ec.idbData = value;
                }
            });
        }
    }

    async evercookie_session_storage(name: string, value: string) {
        if (sessionStorage) {
            if (value !== undefined) {
                sessionStorage.setItem(name, value);
                return sessionStorage.getItem(name);
                // this.session_subject.next(sessionStorage.getItem(name));
            } else {
                return sessionStorage.getItem(name);
                // this.session_subject.next(sessionStorage.getItem(name));
            }
        }
    }

    async evercookie_cookie(name: string, value: string) {
        if (value !== undefined) {
            // expire the cookie first
            this.cookie_service.set(name, undefined, new Date(2010,9,20), "/", this.options.domain);
            this.cookie_service.set(name, value, new Date(2040,12,31), "/", this.options.domain);
            return this.cookie_service.get(name);
            // this.cookie_subject.next(this.cookie_service.get(name));
        } else {
            return this.cookie_service.get(name);
            // this.cookie_subject.next(this.cookie_service.get(name));
        }
    }

    // get value from param-like string (eg, "x=y&name=VALUE")
    getFromStr(name: string, text: string) {
        var nameEQ = name + "=",
            ca = text.split(/[;&]/),
            i: number, c: string;
        for (i = 0; i < ca.length; i++) {
            c = ca[i];
            while (c.charAt(0) === " ") {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
    }

    _ec_replace(str: string, key: string, value: string) {
        if (str.indexOf("&" + key + "=") > -1 || str.indexOf(key + "=") === 0) {
            // find start
            var idx: number = str.indexOf("&" + key + "="),
                end: number, newstr: string;
            if (idx === -1) {
                idx = str.indexOf(key + "=");
            }
            // find end
            end = str.indexOf("&", idx + 1);
            if (end !== -1) {
                newstr = str.substr(0, idx) + str.substr(end + (idx ? 0 : 1)) + "&" + key + "=" + value;
            } else {
                newstr = str.substr(0, idx) + "&" + key + "=" + value;
            }
            return newstr;
        } else {
            return str + "&" + key + "=" + value;
        }
    }
}
