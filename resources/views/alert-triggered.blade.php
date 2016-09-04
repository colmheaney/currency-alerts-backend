<div>
  <h1>Currency exchange alert.</h1>
  <p>Symbol: {{ $alert->symbol }} </p>
  <p>You requseted an alert when the current rate went below {{ $alert->lower_rate }} or above {{ $alert->upper_rate }}</p>
  <p>The current exchange rate is: {{ $rate }} </p>
</div>
