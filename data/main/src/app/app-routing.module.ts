import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  /** Главная стартовая */
  {
    path: '',
    pathMatch: 'full',
    loadChildren: () => import('./modules/pages/pages/page-home/page-home.module').then((m) => m.PageHomeModule),
  },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
