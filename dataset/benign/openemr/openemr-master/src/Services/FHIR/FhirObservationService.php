<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Services\BaseService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationComponent;

class FhirObservationService extends BaseService
{

    public function __construct()
    {
        parent::__construct('form_vitals');
    }

    public function createObservationResource($id = '', $data = '', $encode = true)
    {
        $resource = new FHIRObservation();

        // Check Profile type and add its Category | Status | Note
        switch ($data['formdir']) {
            case 'vitals':
                $code = new FHIRCoding();
                $coding = new FHIRCodeableConcept();
                $code->setSystem("http://terminology.hl7.org/CodeSystem/observation-category");
                $code->setCode("vital-signs");
                $code->setDisplay("Vital Signs");
                $coding->addCoding($code);
                $coding->setText("Vital Signs");
                $resource->addCategory($coding);
                $data['text'] ? $resource->addNote($data['text']) : null;
                $resource->setStatus('final');
                break;
            default:
                #TODO: Return CapabilityStatement or Outcome Resource
                break;
        }

        // Check Profile or Member and add/set appropriately
        if ($data['profile'] == 'vitals') {
            $vital_coding = new FHIRCoding();
            $vital_coding->setCode("85353-1");
            $vital_coding->setSystem("http://loinc.org");
            $vital_coding->setDisplay(
                "Vital signs, weight, height, head circumference, oxygen saturation and BMI panel"
            );
            $vital_code = new FHIRCodeableConcept();
            $vital_code->addCoding($vital_coding);
            $vital_code->setText("Vital signs Panel");
            $resource->setCode($vital_code);
            $this->addMembers($data, $resource);
        } else {
            $this->setValues($data, $resource);
        }

        $subject = new FHIRReference();
        $subject->setReference("Patient/" . $data['pid']);
        $subject->setType("Patient");
        $resource->setSubject($subject);
        $encounter =  new FHIRReference();
        $encounter->setReference("Encounter/" . $data['encounter']);
        $encounter->setType("Encounter");
        $resource->setEncounter($encounter);
        $data['date'] = date("Y-m-d\TH:i:s", strtotime($data['date']));
        $resource->setEffectiveDateTime($data['date']);
        $resource->setId($id);

        if ($encode) {
            return json_encode($resource);
        } else {
            return $resource;
        }
    }

    public function getOne($id)
    {
        $split_id = explode("-", $id);
        $profile = $split_id[0];
        $id = $split_id[1];
        $profile_data = $this->queryFields(
            array(
                "where" => "WHERE form_vitals.id = ?",
                "data" => array($id),
                "join" => "JOIN forms fo on form_vitals.id = fo.form_id",
                "limit" => 1
            )
        );
        $profile_data = $this->filterProfiles($profile_data);
        if (
            $profile_data[$profile] || $profile == 'vitals' ||
            ($profile_data['bps'] || $profile_data["bpd"]) && $profile == "bp"
        ) {
            $profile_data['profile'] = $profile;
        } else {
            return false;
        }
        return $profile_data;
    }

    public function getAll($search)
    {
        if ($search['category']) {
            if ($search['category'] == 'vital-signs') {
                $searchQuery = array(
                    "join" => "JOIN forms fo on form_vitals.id = fo.form_id"
                );
            } else {
                return false;
            }
        } else {
            $searchQuery = array(
                "join" => "JOIN forms fo on form_vitals.id = fo.form_id"
            );
        }

        if ($search['pid'] || $search['date']) {
            $searchQuery["where"] = "WHERE ";
            $searchQuery["data"] = array();
            $whereClauses = array();
        }
        if ($search['pid']) {
            array_push($whereClauses, "form_vitals.pid = ?");
            array_push($searchQuery["data"], $search['pid']);
        }
        if ($search['date']) {
            if ($this->isValidDate($search['date'])) {
                $search['date'] = date("Y/m/d H:i:s", strtotime($search['date']));
                array_push($whereClauses, "form_vitals.date between ? and NOW()");
                array_push($searchQuery["data"], $search['date']);
            } else {
                return false;
            }
        }

        $searchQuery["where"] .= implode(" AND ", $whereClauses);
        return $this->queryFields($searchQuery);
    }

    private function addMembers($data, $resource)
    {
        foreach ($data as $key => $value) {
            if ($key == 'temperature') {
                $temp_refrence = new FHIRReference();
                $temp_refrence->setReference("Observation/temperature-" . $data['form_id']);
                $temp_refrence->setDisplay("Body Temperature");
                $resource->addHasMember($temp_refrence);
            } elseif ($key == 'pulse') {
                $pulse_refrence = new FHIRReference();
                $pulse_refrence->setDisplay("Heart Rate");
                $pulse_refrence->setReference("Observation/pulse-" . $data['form_id']);
                $resource->addHasMember($pulse_refrence);
            } elseif ($key == 'respiration') {
                $respiration_refrence = new FHIRReference();
                $respiration_refrence->setReference("Observation/respiration-" . $data['form_id']);
                $respiration_refrence->setDisplay("Respiratory Rate");
                $resource->addHasMember($respiration_refrence);
            } elseif ($key == 'weight') {
                $weight_refrence = new FHIRReference();
                $weight_refrence->setReference("Observation/weight-" . $data['form_id']);
                $weight_refrence->setDisplay("Body weight");
                $resource->addHasMember($weight_refrence);
            } elseif ($key == 'height') {
                $height_refrence = new FHIRReference();
                $height_refrence->setReference("Observation/height-" . $data['form_id']);
                $height_refrence->setDisplay("Body height");
                $resource->addHasMember($height_refrence);
            } elseif ($key == 'BMI') {
                $BMI_refrence = new FHIRReference();
                $BMI_refrence->setReference("Observation/BMI-" . $data['form_id']);
                $BMI_refrence->setDisplay("Body mass index (BMI) [Ratio]");
                $resource->addHasMember($BMI_refrence);
            } elseif ($key == 'head_circ') {
                $head_circ_refrence = new FHIRReference();
                $head_circ_refrence->setReference("Observation/head_circ-" . $data['form_id']);
                $head_circ_refrence->setDisplay("Head Occipital-frontal circumference");
                $resource->addHasMember($head_circ_refrence);
            } elseif ($key == 'oxygen_saturation') {
                $oxygen_refrence = new FHIRReference();
                $oxygen_refrence->setReference("Observation/oxygen_saturation-" . $data['form_id']);
                $oxygen_refrence->setDisplay("Oxygen saturation in Arterial blood");
                $resource->addHasMember($oxygen_refrence);
            }
        }
        if ($data['bps'] || $data['bpd']) {
            $bp_refrence = new FHIRReference();
            $bp_refrence->setReference("Observation/bp-" . $data['form_id']);
            $bp_refrence->setDisplay("Blood pressure panel with all children optional");
            $resource->addHasMember($bp_refrence);
        }
    }

    private function setValues($data, $resource)
    {
        $quantity = new FHIRQuantity();
        $quantity->setSystem("http://unitsofmeasure.org");
        $coding = new FHIRCoding();
        $coding->setSystem("http://loinc.org");
        switch ($data['profile']) {
            case 'weight':
                $quantity->setValue($data['weight']);
                $quantity->setUnit('lbs');
                $quantity->setCode("[lb_av]");
                $resource->setValueQuantity($quantity);
                $coding->setCode("29463-7");
                $coding->setDisplay("Body weight");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'height':
                $quantity->setValue($data['height']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $resource->setValueQuantity($quantity);
                $coding->setCode("8302-2");
                $coding->setDisplay("Body height");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'bp':
                $coding->setCode("85354-9");
                $coding->setDisplay("Blood pressure panel with all children optional");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $code->setText("Blood pressure systolic & diastolic");
                $resource->setCode($code);
                $error_coding = clone $coding;
                $error_coding->setCode("unknown");
                $error_coding->setSystem("http://terminology.hl7.org/CodeSystem/data-absent-reason");
                $error_coding->setDisplay("Unknown");
                $error_code = new FHIRCodeableConcept();
                $error_code->addCoding($error_coding);

                // Component - Systolic blood pressure
                $bps_component = new FHIRObservationComponent();
                if ($data['bps']) {
                    $bps_quantity = clone $quantity;
                    $bps_quantity->setValue($data['bps']);
                    $bps_quantity->setUnit('mmHg');
                    $bps_quantity->setCode("[mm[Hg]]");
                    $bps_component->setValueQuantity($bps_quantity);
                } else {
                    $bps_component->setDataAbsentReason($error_code);
                }
                $bps_coding = clone $coding;
                $bps_coding->setCode("8480-6");
                $bps_coding->setDisplay("Systolic blood pressure");
                $bps_code = new FHIRCodeableConcept();
                $bps_code->addCoding($bps_coding);
                $bps_component->setCode($bps_code);
                $resource->addComponent($bps_component);

                // Component - Diastolic blood pressure
                $bpd_component = new FHIRObservationComponent();
                if ($data['bpd']) {
                    $bpd_quantity = clone $quantity;
                    $bpd_quantity->setValue($data['bpd']);
                    $bpd_quantity->setUnit('mmHg');
                    $bpd_quantity->setCode("[mm[Hg]]");
                    $bpd_component->setValueQuantity($bpd_quantity);
                } else {
                    $bpd_component->setDataAbsentReason($error_code);
                }
                $bps_coding = clone $coding;
                $bps_coding->setCode("8462-4");
                $bps_coding->setDisplay("Diastolic blood pressure");
                $bps_code = new FHIRCodeableConcept();
                $bps_code->addCoding($bps_coding);
                $bpd_component->setCode($bps_code);
                $resource->addComponent($bpd_component);
                break;
            case 'temperature':
                $quantity->setValue($data['temperature']);
                $quantity->setUnit('F');
                $quantity->setCode("[degF]");
                $resource->setValueQuantity($quantity);
                $cat_coding = new FHIRCoding();
                $cat_coding->setSystem("http://snomed.info/sct");
                $cat_coding->setCode("vital-signs");
                $cat_coding->setDisplay($data['temp_method']);
                $code = new FHIRCodeableConcept();
                $code->addCoding($cat_coding);
                $code->setText("Vital Signs");
                $resource->addCategory($code);
                $coding->setCode("8310-5");
                $coding->setDisplay("Body temperature");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'pulse':
                $quantity->setValue($data['pulse']);
                $quantity->setUnit('beats/minute');
                $quantity->setCode("/min");
                $resource->setValueQuantity($quantity);
                $coding->setCode("8867-4");
                $coding->setDisplay("Heart rate");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'respiration':
                $quantity->setValue($data['respiration']);
                $quantity->setUnit('breaths/minute');
                $quantity->setCode("/min");
                $resource->setValueQuantity($quantity);
                $coding->setCode("9279-1");
                $coding->setDisplay("Respiratory rate");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'BMI':
                $quantity->setValue($data['BMI']);
                $quantity->setUnit('kg/m2');
                $quantity->setCode("kg/m2");
                $height_ref = new FHIRReference();
                $height_ref->setReference("Observation/height-" . $data['id']);
                $height_ref->setDisplay("Body Height");
                $weight_ref = new FHIRReference();
                $weight_ref->setReference("Observation/weight-" . $data['id']);
                $weight_ref->setDisplay("Body Weight");
                $resource->addDerivedFrom($height_ref);
                $resource->addDerivedFrom($height_ref);
                $resource->setValueQuantity($quantity);
                $coding->setCode("39156-5");
                $coding->setDisplay("Body mass index (BMI) [Ratio]");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'head_circ':
                $quantity->setValue($data['head_circ']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $resource->setValueQuantity($quantity);
                $coding->setCode("9843-4");
                $coding->setDisplay("Head Occipital-frontal circumference");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'waist_circ':
                $quantity->setValue($data['waist_circ']);
                $quantity->setUnit('in');
                $quantity->setCode("[in_i]");
                $resource->setValueQuantity($quantity);
                $coding->setCode("8280-0");
                $coding->setDisplay("Waist Circumference at umbilicus by Tape measure");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;
            case 'oxygen_saturation':
                $quantity->setValue($data['oxygen_saturation']);
                $quantity->setUnit('%');
                $quantity->setCode("%");
                $resource->setValueQuantity($quantity);
                $coding->setCode("2708-6");
                $coding->setDisplay("Oxygen saturation in Arterial blood");
                $code = new FHIRCodeableConcept();
                $code->addCoding($coding);
                $resource->setCode($code);
                break;

            default:
                break;
        }
    }

    private function filterProfiles($data)
    {
        return array_filter($data, function ($k) {
            if ($k !== "0.0" && $k !== "0.00" && $k !== null && $k !== '') {
                return $k;
            }
        });
    }
}
