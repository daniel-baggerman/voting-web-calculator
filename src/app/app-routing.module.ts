import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { BeatpathBallotCastComponent } from './beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { AuthenticationService } from './authentication.service';
import { ManageElectionComponent } from './manage-election/manage-election.component';
import { ReportingComponent } from './reporting/reporting.component';

const appRoutes: Routes = [
    { path: '', component: HomePageComponent},
    { path: 'election_search', component: SearchElectionsComponent},
    { path: 'create_election', component: CreateElectionComponent},
    { path: 'manage_election/:election_name',
        component: ManageElectionComponent,
        // canActivateChild: [AuthenticationService],
        children: [
            { path: 'cast_vote', component: BeatpathBallotCastComponent},
            { path: 'reporting', component: ReportingComponent}
        ]
    },
    { path: '**', component: PageNotFoundComponent}
]

@NgModule({
    imports: [RouterModule.forRoot(appRoutes, {useHash: false})],
    exports: [RouterModule]
})
export class AppRoutingModule { 

} 