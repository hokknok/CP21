import { Component, OnDestroy, OnInit } from '@angular/core';
import { UntilDestroy } from '@ngneat/until-destroy';

@UntilDestroy()
@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.scss'],
})
export class FooterComponent implements OnInit, OnDestroy {
  public desc: string;
  public isLoading = false;

  constructor() {}

  // =========================================================================
  // --- Lifecycle Hooks -----------------------------------------------------
  // -------------------------------------------------------------------------

  public ngOnInit(): void {}

  public ngOnDestroy(): void {}

  // =========================================================================
  // --- Публичные и методы шаблона ------------------------------------------
  // -------------------------------------------------------------------------

  // =========================================================================
  // --- Вспомогательные методы ----------------------------------------------
  // -------------------------------------------------------------------------
}
