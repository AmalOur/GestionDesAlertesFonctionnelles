import pandas as pd
import seaborn as sns
import matplotlib.pyplot as plt
from datetime import datetime, timedelta

certificates_df = pd.read_csv('Records_alert.csv')

certificates_df['date_fin'] = pd.to_datetime(certificates_df['date_fin'])

certificates_df['jours_avant_expiration'] = (certificates_df['date_fin'] - pd.to_datetime('today')).dt.days

certificates_df = certificates_df.sort_values('jours_avant_expiration', ascending=False)

num_records = len(certificates_df)
color_palette = sns.color_palette("viridis", n_colors=num_records)

certificates_df['x_label'] = certificates_df['record_type'].str.cat(certificates_df['application_source'], sep='\n')

plt.figure(figsize=(13, 10))
bars = sns.barplot(x=certificates_df['x_label'], 
                   y=certificates_df['jours_avant_expiration'], 
                   palette=color_palette)

plt.xlabel('Infos', fontsize=14)
plt.ylabel('Jours avant expiration', fontsize=14)
plt.title('Alerte d\'expiration', fontsize=16, fontweight='bold')

plt.ylim(0, 150)

plt.xticks(fontsize=12, fontweight='bold')
plt.yticks(fontsize=12, fontweight='bold')

for bar, days in zip(bars.patches, certificates_df['jours_avant_expiration']):
    bars.annotate(f'{days} jours', 
                  (bar.get_x() + bar.get_width() / 2, bar.get_height()), 
                  ha='center', va='bottom', fontsize=12, fontweight='bold', color='black')

plt.tight_layout()
plt.show()