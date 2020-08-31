export class cookie {
    constructor(public userData?: string,
                public cookieData?: string,
                public localData?: string,
                public globalData?: string,
                public sessionData?: string,
                public windowData?: string,
                public historyData?: string,
                public hstsData?: string,
                public dbData?: string,
                public idbData?: string,
                public etagData?: string,
                public cacheData?: string,
                public javaData?: string,
                public pngData?: string,
                public slData?: string,
                public lsoData?: string){
    }
}