 <div>
     <div class="body__cards">

         <section class="session-resume">
             <p class="session-resume__head">[ Last session content ]</p>

             <div class="session-resume__content">
                 <div class="session-resume__item">
                     <span class="session-resume__label">Topic</span>
                     <span class="session-resume__value">: {{ $lastRoot->topic ?? '-' }}</span>
                 </div>
                 <div class="session-resume__item">
                     <span class="session-resume__label">Drift</span>
                     <span class="session-resume__value">: Moderate</span>
                 </div>
                 <div class="session-resume__item">
                     <span class="session-resume__label">File</span>
                     <span
                         class="session-resume__value">:{{ ' ' . 'semantic_tree_' . ($lastRoot->id ?? 'x') . '.txt' }}</span>
                 </div>
                 {{-- <div class="session-resume__item">
                     <span class="session-resume__label">Similar</span>
                     <span
                         class="session-resume__value">:{{ ' ' . 'similarities_' . ($lastRoot->id ?? 'x') . '.txt' }}</span>
                 </div> --}}
             </div>
         </section>


         @if ($strangeIdea)
             <div class="panel panel--strange-idea">
                 <div class="panel__heading">[ STRANGE IDEA RETRIEVED ]</div>
                 <div class="panel__body">
                     <div class="panel__quote" style="font-size: 0.75rem">
                         <span class="panel__label">&gt;</span>
                         “{{ $strangeIdea->idea }}”
                     </div>
                     <div class="panel__meta">
                         ↳ source: <span class="panel__source">{{ $strangeIdea->source }}</span>
                         / confidence:
                         <span class="panel__confidence">{{ $strangeIdea->confidence }}</span>
                     </div>
                 </div>
             </div>
         @endif




     </div>

     <div style="display: flex; flex-direction: column; gap: 1rem;">
         > ACTIVE SEMANTIC NETWORK



         <table class="semantic-table">
             <thead class="semantic-table__head">
                 <tr class="semantic-table__row semantic-table__row--head">
                     <th class="semantic-table__cell semantic-table__cell--head">id</th>
                     <th class="semantic-table__cell semantic-table__cell--head">file</th>
                     <th class="semantic-table__cell semantic-table__cell--head">topic</th>
                     <th class="semantic-table__cell semantic-table__cell--head">cosine</th>
                     <th class="semantic-table__cell semantic-table__cell--head">link</th>
                 </tr>
             </thead>
             <tbody class="semantic-table__body">
                 @foreach ($roots as $i => $node)
                     <tr class="semantic-table__row">
                         <td class="semantic-table__cell">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</td>
                         <td class="semantic-table__cell semantic-table__cell--file">
                             {{ basename($node->file_path) ?? 'error in system' }}
                         </td>
                         <td class="semantic-table__cell">{{ $node->topic }}</td>
                         <td class="semantic-table__cell semantic-table__cell--cosine">
                             {{ number_format($node->cosine_score ?? 0.9, 3) }}</td>
                         <td class="semantic-table__cell semantic-table__cell--cosine">
                             <a href="/trees/{{ $node->file_path }}" target="_blank">
                                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                     <path
                                         d="M3.9 12C3.9 10.29 5.29 8.9 7 8.9H11V7H7C4.24 7 2 9.24 2 12C2 14.76 4.24 17 7 17H11V15.1H7C5.29 15.1 3.9 13.71 3.9 12ZM8 13H16V11H8V13ZM17 7H13V8.9H17C18.71 8.9 20.1 10.29 20.1 12C20.1 13.71 18.71 15.1 17 15.1H13V17H17C19.76 17 22 14.76 22 12C22 9.24 19.76 7 17 7Z"
                                         fill="#C6C9FA" />
                                 </svg>
                             </a>
                         </td>
                     </tr>
                 @endforeach
             </tbody>
         </table>

         <div class="mobile-table">
             @foreach ($roots as $i => $node)
                 <div class="semantic-card">
                     <div class="semantic-card__heading">
                         <div class="semantic-card__id">#{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</div>
                         <div class="semantic-card__file">{{ basename($node->file_path) ?? 'error in system' }}</div>
                     </div>

                     <div class="semantic-card__meta">
                         <div class="semantic-card__meta-block">
                             <span class="semantic-card__label">Topic</span>
                             <span class="semantic-card__value">{{ $node->topic }}</span>
                         </div>
                         <div class="semantic-card__meta-block">
                             <span class="semantic-card__label">Cosine</span>
                             <span class="semantic-card__value semantic-card__value--cosine">
                                 {{ number_format($node->cosine_score ?? 0.9, 3) }}
                             </span>
                         </div>
                     </div>

                     <div class="semantic-card__footer">
                         <a href="/trees/{{ $node->file_path }}" class="semantic-card__link" target="_blank">
                             <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                 <path
                                     d="M16 10H17V17H16V18H15V19H14V20H13V21H12V22H11V23H5V22H4V21H3V20H2V19H1V14H2V13H3V12H4V11H5V14H4V15H3V18H4V19H5V20H6V21H10V20H11V19H12V18H13V17H14V16H15V11H14V10H13V9H14V8H15V9H16V10Z"
                                     fill="#C6C9FA" />
                                 <path
                                     d="M23 5V10H22V11H21V12H20V13H19V10H20V9H21V6H20V5H19V4H18V3H14V4H13V5H12V6H11V7H10V8H9V13H10V14H11V15H10V16H9V15H8V14H7V7H8V6H9V5H10V4H11V3H12V2H13V1H19V2H20V3H21V4H22V5H23Z"
                                     fill="#C6C9FA" />
                             </svg>
                             <span>View Link</span>
                         </a>
                     </div>
                 </div>
             @endforeach
         </div>

     </div>

 </div>
