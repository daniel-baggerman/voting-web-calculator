export class election{
    constructor(public election_id: number,
                public description: string,
                public long_description: string,
                public options?: string[],
                public start_date?: string,
                public end_date?: string,
                public public_private?: number,  // 1 or 0, 1 = public
                public password_protect?: number, // 1 or 0, 1 = yes
                public password?: string,
                public url_election_name?: string,
                public anon_results?: number,    // 1 or 0, 1 = yes
                ){
    }
}