<template>

  <GmapMap :center="center"
    :zoom="zoom" ref = "map">
    <gmap-cluster>
    
      <gmap-marker ref="marker"
        :key ="index"
        v-for="(m, index) in markers"
        :position="m.position"
        :clickable="true"
        :icon.path="{url:'/icons/'+ m.incident + '.png'}"
        :draggable="false"
        @click="toggleInfoWindow(m,index)">
      </gmap-marker>
    
    </gmap-cluster>

    <gmap-info-window :options="infoOptions" :position="infoWindowPos" :opened="infoWinOpen" @closeclick="infoWinOpen=false">
      <info-content :address='address' :is-responder="true" :content="infoContent" :reporter="reporter" :contact-number="contactNumber" :report-date="report_date"></info-content>
    </gmap-info-window>  
  </GmapMap>
</template>
<script>

import InfoContent from './InfoContent.vue'
import GmapCluster from 'vue2-google-maps/dist/components/cluster' // replace src with dist if you have Babel issues
 
Vue.component('GmapCluster', GmapCluster)

export default {
      components:{
          'info-content':InfoContent
      },

      props:['userId', 'userLat', 'userLng'],

        // mounted() {
        //      this.geoLocationInit();
        // },

        data(){
          return {
            center: {
              lat:0,
              lng:0
            },
            zoom:18,
            markers:[],
            infoContent: '',
            infoWindowPos: {
                lat: 0,
                lng: 0
            },
            reporter:'',
            report_date:'',
            contactNumber:'',
            incidentIcon:'',
            address:'',
            infoWinOpen: false,
            currentMidx: null,
            infoOptions: {
                pixelOffset: {
                    width: 0,
                    height: -35
                }
            },
            radius:20,
            status:0,
            geolocated:false,
          }
        },

        mounted(){
          this.geoLocationInit();
          this.initMap();
        },

        methods: {
          initMap() {

            if((this.userLat==0|| this.userLat==null) && (!this.geolocated)) {
              this.center = {lat:11.92,lng:122.63};
              this.zoom = 5.5;
            }
            else {
              this.center.lat=parseFloat(this.userLat);
              this.center.lng=parseFloat(this.userLng);
              this.zoom=18;
            }
            this.fetchReports();
          },

          geoLocationInit (){
            if(navigator.geolocation){
              navigator.geolocation.getCurrentPosition(this.success,this.fail);
            } else {
              alert("Browser not supported");
            }
          },

          success: function(position){
            let center = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            this.center=center;
            this.zoom=18;
            Bus.$emit('marker_changed',this.center);
            this.geolocated=true;
          },

          fail: function(error){
            this.geolocated=false;
            console.log("error:", error);
          },

          toggleInfoWindow (marker, idx) {

            this.infoWindowPos = marker.position;
            this.infoContent = marker.report;
            this.incident = "/icons/"+ marker.incident+".png";
            this.address = marker.name;
            this.contactNumber = marker.contact_number;
            this.reporter = marker.reporter;
            this.report_date = marker.report_date;
            console.log("showinf");

            //check if its the same marker that was selected if yes toggle
            if (this.currentMidx == idx) {
                this.infoWinOpen = !this.infoWinOpen;
            }
            //if different marker set infowindow to open and reset current marker index
            else {
                this.infoWinOpen = true;
                this.currentMidx = idx;
            }
          },
          fetchReports(){
              axios.post('/api/recent-reports',{place: this.center, radius:this.radius, status:this.status, id:this.userId}).then(response=> {
                      let data = response.data;
                      this.markers = data.markers;
                      Bus.$emit('reports_fetched',data);
              });
          },
        },

         created(){
                        
            Bus.$on('currentloc_changed', data=> {
              this.center=data;
            })

            Bus.$on('reports_fetched',data=>{
              this.markers=data.markers;
              if(this.markers.length>0){
                  this.center=data.markers[0].position;
              }
              console.log('event data',data);
            })

            Bus.$on('place_filter',place=>{
              this.center=place;
              this.fetchReports();
            })

            Bus.$on('marker_result_clicked', item=> {
              console.log("report id",item);
              let targetMarker= _.find(this.markers, (o) => o.id === item.report_id);
              this.center=targetMarker.position;
              this.zoom=20;
              console.log("target",targetMarker);
              this.toggleInfoWindow(targetMarker,targetMarker.id);
            })

            Bus.$on('changed_radius', data=> {
              this.radius = data;
              this.fetchReports();
            })

            Bus.$on('changed_status', data=> {
              this.status = data;
              this.fetchReports();
            })

            Bus.$on('respond_to_report', item=> {
              //this.respondToReport(index);
              console.log("respond_report",item);
              for (var i=0; i < this.markers.length; i++) {
                if (this.markers[i].id === item.report_id) {
                    Bus.$emit('selectReport',this.markers[i]);
                }
              }
            })
            
             Bus.$on('reports_changed', data=>{
              this.fetchReports();
            })

             this.$on('bounds_changed', data=>{
              this.fetchReports();
             })
         },
    };
</script>