import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { BeatpathBallotCastComponent } from './beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';

const appRoutes: Routes = [
    { path: '', component: HomePageComponent},
    { path: 'create_poll', component: CreateElectionComponent},
    { path: 'beatpath/cast_ballot', component: BeatpathBallotCastComponent},
    { path: '**', component: PageNotFoundComponent}
]

@NgModule({
    imports: [RouterModule.forRoot(appRoutes, {useHash: false})],
    exports: [RouterModule]
})
export class AppRoutingModule { 

} 