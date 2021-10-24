import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TrendItemComponent } from './trend-item.component';

@NgModule({
  declarations: [TrendItemComponent],
  exports: [TrendItemComponent],
  imports: [CommonModule],
})
export class TrendItemModule {}
