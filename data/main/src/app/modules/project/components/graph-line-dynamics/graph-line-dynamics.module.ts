import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgApexchartsModule } from 'ng-apexcharts';
import { TrendItemModule } from '../trend-item/trend-item.module';
import { GraphLineDynamicsComponent } from './graph-line-dynamics.component';

@NgModule({
  declarations: [GraphLineDynamicsComponent],
  exports: [GraphLineDynamicsComponent],
  imports: [CommonModule, NgApexchartsModule, TrendItemModule],
})
export class GraphLineDynamicsModule {}
